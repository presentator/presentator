<script>
    import { onMount } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import {
        screens,
        activeScreen,
        transitionScreen,
        fitToScreen,
        activeScale,
        mode,
        transition,
        modes,
        showPreviewHotspots,
        showPreviewAnnotations,
    } from "@/stores/screens";
    import { activePrototype } from "@/stores/prototypes";
    import {
        activeScreenPrimaryComments,
        selectedComment,
        addComment,
        showResolved,
    } from "@/stores/comments";
    import {
        activeScreenHotspots,
        selectedHotspot,
        addHotspot,
        hotspotTypes,
        isHotspotDragging,
    } from "@/stores/hotspots";
    import { activeProject } from "@/stores/projects";
    import { loggedUser } from "@/stores/app";
    import tooltip from "@/actions/tooltip";
    import LazyImg from "@/components/base/LazyImg.svelte";
    import CommentPin from "@/components/comments/CommentPin.svelte";
    import Hotspot from "@/components/hotspots/Hotspot.svelte";
    import ScreenOverlay from "@/components/screens/ScreenOverlay.svelte";

    const defaultBG = "var(--baseColor)";

    let screenPreview;
    let transitionScreenPreview;
    let debounceTimeouts = {};
    let updateScaleTimeout;
    let tooltipOptions = undefined;
    let hints = false;
    let hintsTimeout = false;
    let isMainLoading = true;

    $: width = $activePrototype?.size?.split("x")?.[0] << 0;

    $: height = $activePrototype?.size?.split("x")?.[1] << 0;

    $: hAlignScreen($activeScreen?.alignment, screenPreview);

    $: if (!$fitToScreen) {
        debounce(() => hAlignScreen($activeScreen?.alignment, screenPreview), "", 0);
    }

    $: if ($activeScreen || $activePrototype || $fitToScreen) {
        resetHints();
        updateActiveScale();
    }

    $: if ($mode == modes.hotspots && !$selectedHotspot && !$isHotspotDragging) {
        tooltipOptions = {
            position: "follow",
            text: "Click and drag to create hotspot",
            sub: 'Hold "Alt" to snap',
        };
    } else if ($mode == modes.comments && !$selectedComment) {
        tooltipOptions = { position: "follow", text: "Click to leave a comment" };
    } else {
        tooltipOptions = undefined;
    }

    $: isProjectOwner = $loggedUser?.id && $activeProject?.expand?.users?.find((u) => u.id == $loggedUser.id);

    function updateActiveScale() {
        let newScale = $activePrototype?.scale;

        if (!$activeScreen || (newScale && !$fitToScreen)) {
            $activeScale = newScale;
            return;
        }

        utils.loadImage(pb.files.getUrl($activeScreen, $activeScreen.file)).then((data) => {
            if (!data.success || !data.width) {
                return;
            }

            clearTimeout(updateScaleTimeout);
            updateScaleTimeout = setTimeout(() => {
                if (!screenPreview || !$activeScreen?.id) {
                    return;
                }

                // fit/rescale based on the original activeScale
                const scaledWidth = newScale > 0 ? data.width * newScale : data.width;

                if (screenPreview.clientWidth < scaledWidth) {
                    // recalculate according to the fit to screen ratio
                    newScale = screenPreview.clientWidth / data.width || 1;
                } else if (!newScale) {
                    newScale = 1;
                }

                $activeScale = newScale;
            }, 0);
        });
    }

    function debounce(func, key = "", timeout = 100) {
        key = key || "default";
        clearTimeout(debounceTimeouts[key]);
        debounceTimeouts[key] = setTimeout(func, timeout);
    }

    function hAlignScreen(alignment, container) {
        container = container || screenPreview;
        if (!container) {
            return;
        }

        if (alignment == "left") {
            container.scrollLeft = 0;
        } else if (alignment == "right") {
            container.scrollLeft = container.scrollWidth;
        } else {
            container.scrollLeft = (container.scrollWidth - container.offsetWidth) / 2;
        }
    }

    function newComment(e) {
        if ($mode != modes.comments || $selectedComment) {
            return; // not comments mode or has already selected comment
        }

        e.preventDefault();

        const comment = {
            id: "",
            left: e.offsetX / $activeScale,
            top: e.offsetY / $activeScale,
            message: "",
            screen: $activeScreen?.id,
        };

        addComment(comment, true);
    }

    function newHotspot(e) {
        if ($mode != modes.hotspots || $selectedHotspot) {
            return; // not hotspots mode or has already selected hotpost
        }

        utils.normalizePointerEvent(e);

        // preventDefault is not allowed for passive touch events
        if (!e.touches) {
            e.preventDefault();
        }

        const hotspot = {
            id: "",
            left: e.offsetX / $activeScale,
            top: e.offsetY / $activeScale,
            width: 25,
            height: 25,
            type: hotspotTypes.screen,
            settings: {},
            screen: $activeScreen?.id,
            startResizing: e,
        };

        addHotspot(hotspot, true);
    }

    function resetHints() {
        clearTimeout(hintsTimeout);
        hints = false;
    }

    function showHints() {
        if ($mode != modes.preview) {
            return;
        }

        hints = true;

        clearTimeout(hintsTimeout);
        hintsTimeout = setTimeout(() => {
            hints = false;
        }, 500);
    }

    onMount(() => {
        // eager load the first couple screens to minimize flickering
        for (let screen of $screens.slice(0, 50)) {
            utils.loadImage(pb.files.getUrl(screen, screen.file));
        }

        return () => {
            clearTimeout(hintsTimeout);
            clearTimeout(updateScaleTimeout);
            for (let k in debounceTimeouts) {
                clearTimeout(debounceTimeouts[k]);
            }
        };
    });
</script>

<svelte:window
    on:resize={() => {
        debounce(() => {
            hAlignScreen($activeScreen?.alignment, screenPreview);
            updateActiveScale();
        });
    }}
/>

<div
    class="screen-preview-holder {$activePrototype.size ? 'mobile' : 'desktop'}"
    class:in-transition={!!$transition}
    class:hints={hints || $showPreviewHotspots}
    class:annotations={$showPreviewAnnotations}
>
    <div class="transition-container transition-{$transition}" class:active={$transition}>
        <div class="transition-item old">
            <div
                bind:this={screenPreview}
                class="screen-preview align-{$activeScreen.alignment || 'center'}"
                class:fit-to-screen={$fitToScreen}
                style:width={width ? `${width}px` : undefined}
                style:height={height ? `${height}px` : undefined}
                style:background-color={$activeScreen.background || defaultBG}
            >
                {#if $activeScreen.fixedHeader > 0 && $mode == modes.preview}
                    <div
                        class="fixed-screen-header"
                        style:height="{$activeScreen.fixedHeader * $activeScale}px"
                        style:margin-top="-{$activeScreen.fixedHeader * $activeScale}px"
                    >
                        <div class="fixed-screen-overflow">
                            <div class="fixed-screen-hotspots-wrapper">
                                <LazyImg
                                    class="screen-preview-img"
                                    src={pb.files.getUrl($activeScreen, $activeScreen.file)}
                                    alt={$activeScreen.title}
                                    draggable={false}
                                    loaderClass="hidden"
                                    fetchpriority="high"
                                    scale={$activePrototype.scale}
                                    on:click={showHints}
                                >
                                    <div class="hotspots">
                                        {#each $activeScreenHotspots as hotspot ("header_" + hotspot.id)}
                                            <Hotspot preview {hotspot} />
                                        {/each}
                                    </div>
                                </LazyImg>
                            </div>
                        </div>
                    </div>
                {/if}

                <div
                    class="screen-preview-img-wrapper active-screen-preview-wrapper"
                    class:loading={isMainLoading}
                >
                    <div class="tooltip-wrapper" use:tooltip={tooltipOptions}>
                        <LazyImg
                            bind:isLoading={isMainLoading}
                            class="screen-preview-img"
                            src={pb.files.getUrl($activeScreen, $activeScreen.file)}
                            alt={$activeScreen.title}
                            loading="eager"
                            loaderClass="loader"
                            fetchpriority="high"
                            draggable={false}
                            scale={$activePrototype.scale}
                            on:load={(e) => {
                                e.detail?.scrollIntoView(top);
                                hAlignScreen($activeScreen.alignment);
                                debounce(() => {
                                    utils.triggerEvent("mainScreenLoaded");
                                }, "mainScreenLoaded");
                            }}
                            on:mousedown={(e) => {
                                if (e.button == 0) {
                                    newHotspot(e);
                                }
                            }}
                            on:touchstart={newHotspot}
                            on:click={showHints}
                            on:click={newComment}
                        />
                    </div>

                    {#if $mode == modes.hotspots || $mode == modes.preview}
                        <div class="hotspots">
                            {#each $activeScreenHotspots as hotspot (hotspot.id)}
                                <Hotspot {hotspot} preview={$mode == modes.preview} />
                            {/each}
                        </div>

                        <ScreenOverlay />
                    {:else if $mode == modes.comments}
                        <div class="comment-pins">
                            {#each $activeScreenPrimaryComments as comment, i (comment.id)}
                                {#if !comment.resolved || $showResolved}
                                    <CommentPin disabled={!isProjectOwner} {comment}>
                                        {comment.id ? i + 1 : ""}
                                    </CommentPin>
                                {/if}
                            {/each}
                        </div>
                    {/if}
                </div>

                {#if $activeScreen.fixedFooter > 0 && $mode == modes.preview}
                    <div
                        class="fixed-screen-footer"
                        style:height="{$activeScreen.fixedFooter * $activeScale}px"
                        style:margin-top="-{$activeScreen.fixedFooter * $activeScale}px"
                    >
                        <div class="fixed-screen-overflow">
                            <div class="fixed-screen-hotspots-wrapper">
                                <LazyImg
                                    class="screen-preview-img"
                                    src={pb.files.getUrl($activeScreen, $activeScreen.file)}
                                    alt={$activeScreen.title}
                                    draggable={false}
                                    loading="eager"
                                    loaderClass="hidden"
                                    fetchpriority="high"
                                    scale={$activePrototype.scale}
                                    on:click={showHints}
                                >
                                    <div class="hotspots">
                                        {#each $activeScreenHotspots as hotspot ("footer_" + hotspot.id)}
                                            <Hotspot preview {hotspot} />
                                        {/each}
                                    </div>
                                </LazyImg>
                            </div>
                        </div>
                    </div>
                {/if}
            </div>
        </div>

        <!-- transition screen -->
        {#if $transition && $transitionScreen?.id}
            <div class="transition-item new">
                <div
                    bind:this={transitionScreenPreview}
                    class="screen-preview align-{$activeScreen.alignment || 'center'}"
                    class:fit-to-screen={$fitToScreen}
                    style:width={width ? `${width}px` : undefined}
                    style:height={height ? `${height}px` : undefined}
                    style:background-color={$transitionScreen.background || defaultBG}
                >
                    {#if $transitionScreen.fixedHeader > 0 && $mode == modes.preview}
                        <div
                            class="fixed-screen-header"
                            style:height="{$transitionScreen.fixedHeader * $activeScale}px"
                            style:margin-top="-{$transitionScreen.fixedHeader * $activeScale}px"
                        >
                            <div class="fixed-screen-overflow">
                                <LazyImg
                                    class="screen-preview-img"
                                    src={pb.files.getUrl($transitionScreen, $transitionScreen.file)}
                                    alt={$transitionScreen.title}
                                    draggable={false}
                                    loaderClass="hidden"
                                    fetchpriority="high"
                                    scale={$activePrototype.scale}
                                />
                            </div>
                        </div>
                    {/if}

                    <div class="screen-preview-img-wrapper">
                        <LazyImg
                            class="screen-preview-img"
                            src={pb.files.getUrl($transitionScreen, $transitionScreen.file)}
                            alt={$transitionScreen.title}
                            loading="eager"
                            loaderClass="loader"
                            fetchpriority="high"
                            draggable={false}
                            scale={$activePrototype.scale}
                            on:load={() => hAlignScreen($transitionScreen.alignment, transitionScreenPreview)}
                        />
                    </div>

                    {#if $transitionScreen.fixedFooter > 0 && $mode == modes.preview}
                        <div
                            class="fixed-screen-footer"
                            style:height="{$transitionScreen.fixedFooter * $activeScale}px"
                            style:margin-top="-{$transitionScreen.fixedFooter * $activeScale}px"
                        >
                            <div class="fixed-screen-overflow">
                                <LazyImg
                                    class="screen-preview-img"
                                    src={pb.files.getUrl($transitionScreen, $transitionScreen.file)}
                                    alt={$transitionScreen.title}
                                    loading="eager"
                                    loaderClass="hidden"
                                    fetchpriority="high"
                                    scale={$activePrototype.scale}
                                />
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        {/if}
    </div>
</div>

<style lang="scss">
    // prevent elements flickering while loading
    .active-screen-preview-wrapper.loading {
        .comment-pins,
        .hotspots {
            opacity: 0;
        }
    }
</style>
