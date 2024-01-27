<script>
    import { onDestroy, tick } from "svelte";
    import { slide } from "svelte/transition";
    import { push, querystring, replace } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import { prototypes, activePrototype, activePrototypeId } from "@/stores/prototypes";
    import {
        resetScreensStore,
        activeScreen,
        screens,
        changeActiveScreenByOffset,
        mode,
        modes,
        changeMode,
        loadModeFromUrl,
        fitToScreen,
        toggleFitToScreen,
        loadFitToScreen,
        getScreenOrder,
        initScreensSubscription,
        unsubscribeScreensFunc,
    } from "@/stores/screens";
    import {
        loadComments,
        isLoadingComments,
        selectedComment,
        activeScreenUnresolvedPrimaryComments,
        initCommentsSubscription,
        unsubscribeCommentsFunc,
    } from "@/stores/comments";
    import { loadHotspots, initHotspotsSubscription, unsubscribeHotspotsFunc } from "@/stores/hotspots";
    import {
        loadTemplates,
        initTemplatesSubscription,
        unsubscribeHotspotTemplatesFunc,
    } from "@/stores/templates";
    import { activeScreenNotifications } from "@/stores/notifications";
    import ScreensBar from "@/components/screens/ScreensBar.svelte";
    import ScreenPreview from "@/components/screens/ScreenPreview.svelte";
    import ScreenPreviewOptionsBtn from "@/components/screens/ScreenPreviewOptionsBtn.svelte";
    import CommentsPanel from "@/components/comments/CommentsPanel.svelte";
    import CommentPopover from "@/components/comments/CommentPopover.svelte";
    import PrototypesSelect from "@/components/prototypes/PrototypesSelect.svelte";
    import ProjectInfoBtn from "@/components/projects/ProjectInfoBtn.svelte";

    const initialQueryParams = utils.getHashQueryParams();

    export let link;
    export let params;

    let isLoading = false;
    let screensBarActive = false;
    let controlsBarActive = true;
    let showResponsiveControls = false;
    let commentsPanel;
    let screenNavWrapper;
    let lastActiveScreenId = "";
    let lastActivePrototypeId = "";

    loadModeFromUrl();
    loadFitToScreen();

    $: if (!link?.allowComments && $mode == modes.comments) {
        changeMode(modes.preview);
    }

    $: if ($activePrototype && lastActivePrototypeId != $activePrototype?.id) {
        lastActivePrototypeId = $activePrototype.id;
        load();
    }

    // handle back browser button navigation
    $: if ($activeScreen && lastActiveScreenId == $activeScreen.id && params.screenId != lastActiveScreenId) {
        lastActiveScreenId = params.screenId;
        $activeScreen = params.screenId;
    }

    // update url on screen change
    $: if ($activeScreen && $activeScreen.id != lastActiveScreenId) {
        lastActiveScreenId = $activeScreen.id;
        push(
            `/${link.username}/prototypes/${$activePrototype.id}/screens/${$activeScreen.id}?${$querystring}`,
        );
    }

    $: isLoaded = !isLoading && !!$activePrototype?.id;

    $: activeScreenIndex = $screens.findIndex((v) => v.id == $activeScreen?.id);

    $: isFirstScreen = activeScreenIndex === 0;

    $: isLastScreen = $screens.length - 1 === activeScreenIndex;

    $: utils.replaceHashQueryParams({ commentId: $selectedComment?.id || null });

    async function load() {
        isLoading = true;

        await tick(); // ensures that isLoaded is updated before the store changes

        resetScreensStore();

        try {
            const prototypeId = $activePrototype.id;

            initHotspotsSubscription(prototypeId);
            initTemplatesSubscription(prototypeId);
            initScreensSubscription(prototypeId);
            if (link?.allowComments) {
                initCommentsSubscription(prototypeId);
            }

            loadTemplates(prototypeId);
            loadHotspots(prototypeId);

            const items = await pb.collection("screens").getFullList({
                filter: `prototype="${prototypeId}"`,
            });

            $activeScreen = params.screenId;
            $screens = utils.sortItemsByIds(items, $activePrototype.screensOrder);

            if (link?.allowComments) {
                // note: the comments should be loaded after the screens
                // to avoid races and complicated state checks
                loadComments(prototypeId, initialQueryParams.commentId);
            }

            // make sure to at least update the url with the matching prototype
            if (!$activeScreen?.id) {
                replace(`/${link.username}/prototypes/${$activePrototype.id}?${$querystring}`);
            }

            isLoading = false;
        } catch (err) {
            if (!err.isAbort) {
                isLoading = false;
                pb.error(err);
            }
        }
    }

    function toggleScreensBar() {
        screensBarActive = !screensBarActive;
    }

    function toggleControlsBar() {
        controlsBarActive = !controlsBarActive;
        if (!controlsBarActive) {
            screensBarActive = false;
            commentsPanel?.hide();
        }
    }

    const excludeNavSelectors = ".toggler-container > .active";

    function onKeydownNav(e) {
        if (e.altKey || e.ctrlKey || utils.isInput(e.target) || document.querySelector(excludeNavSelectors)) {
            return;
        }

        if (e.code === "Escape" && screensBarActive) {
            e.preventDefault();
            screensBarActive = false;
            return;
        }

        if (e.code === "ArrowLeft") {
            e.preventDefault();
            changeActiveScreenByOffset(-1);
            return;
        }

        if (e.code === "ArrowRight") {
            e.preventDefault();
            changeActiveScreenByOffset(1);
            return;
        }

        if (e.code === "KeyP") {
            e.preventDefault();
            changeMode(modes.preview);
            return;
        }

        if (e.code === "KeyC" && link?.allowComments) {
            e.preventDefault();
            changeMode(modes.comments);
            return;
        }
    }

    onDestroy(() => {
        unsubscribeCommentsFunc?.();
        unsubscribeScreensFunc?.();
        unsubscribeHotspotsFunc?.();
        unsubscribeHotspotTemplatesFunc?.();
    });
</script>

<svelte:window on:keydown={onKeydownNav} />

{#if !isLoaded}
    <span class="loader loader-lg m-t-auto m-b-auto" />
{:else if !$activeScreen?.id}
    <div class="wrapper wrapper-sm m-t-auto m-b-auto">
        <div class="placeholder-block">
            <figure class="icon">
                <i class="iconoir-media-image-list" />
            </figure>
            <h5 class="title txt-hint">The prototype doesn't have any screens yet.</h5>
        </div>
    </div>
{:else}
    <div class="screen-preview-container entrance-fade">
        <div
            bind:this={screenNavWrapper}
            class="screen-preview-nav-wrapper"
            class:discreet={!controlsBarActive}
        >
            <!-- svelte-ignore a11y-click-events-have-key-events -->
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <div
                class="screen-preview-nav-ctrl left"
                class:disabled={isFirstScreen}
                on:click|preventDefault={() => changeActiveScreenByOffset(-1)}
            >
                <i class="iconoir-nav-arrow-left" />
            </div>

            <!-- svelte-ignore a11y-click-events-have-key-events -->
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <div
                class="screen-preview-nav-ctrl right"
                class:disabled={isLastScreen}
                on:click|preventDefault={() => changeActiveScreenByOffset(1)}
            >
                <i class="iconoir-nav-arrow-right" />
            </div>

            <ScreenPreview />
        </div>

        {#if $mode === modes.comments}
            <CommentsPanel bind:this={commentsPanel} />
        {/if}
    </div>
{/if}

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
<div class="controls-bar-wrapper">
    <div
        class="controls-bar-toggle"
        class:active={controlsBarActive}
        on:click|preventDefault={toggleControlsBar}
    >
        {controlsBarActive ? "Hide" : "Show"}
    </div>

    {#if controlsBarActive}
        <nav
            class="controls-bar"
            class:show-responsive-controls={showResponsiveControls}
            transition:slide={{ duration: 150 }}
            on:click|preventDefault={() => {
                document.activeElement.blur(); // clears the focus from the btn
            }}
        >
            {#if $activeScreen?.id}
                <div class="controls-group group-left">
                    <button
                        type="button"
                        class="btn btn-sm btn-hint txt-hint {screensBarActive
                            ? 'btn-semitransparent'
                            : 'btn-transparent'}"
                        on:click|preventDefault={toggleScreensBar}
                    >
                        <span class="screen-title-trim" title={$activeScreen?.title}>
                            {$activeScreen?.title}
                        </span>
                        ({getScreenOrder($activeScreen)} of {$screens.length})
                        <i class="iconoir-nav-arrow-{screensBarActive ? 'up' : 'down'}" />
                    </button>
                </div>

                <div class="controls-group group-center">
                    <button
                        class="btn btn-circle {$mode == modes.preview
                            ? 'btn-primary btn-transparent'
                            : 'btn-hint btn-transparent txt-hint'}"
                        use:tooltip={{ position: "top", text: "Preview mode\n(P)" }}
                        on:click|preventDefault={() => changeMode(modes.preview)}
                    >
                        <i class="iconoir-play" />
                    </button>

                    {#if link?.allowComments}
                        <button
                            class="btn btn-circle {$mode == modes.comments
                                ? 'btn-danger btn-transparent'
                                : 'btn-hint btn-transparent txt-hint'}"
                            class:btn-loading={$isLoadingComments}
                            use:tooltip={{
                                position: "top",
                                text: "Comments mode\n(C)",
                            }}
                            on:click|preventDefault={() => changeMode(modes.comments)}
                        >
                            <i class="iconoir-message-text" />

                            {#if $activeScreenUnresolvedPrimaryComments.length}
                                <sub
                                    class="sub {$activeScreenNotifications.length
                                        ? 'sub-warning'
                                        : $mode == modes.comments
                                          ? 'sub-danger'
                                          : 'sub-secondary-alt'}"
                                    title="Unread comments"
                                    >{$activeScreenUnresolvedPrimaryComments.length}</sub
                                >
                            {/if}
                        </button>
                    {/if}

                    <button
                        type="button"
                        class="btn btn-circle btn-hint txt-hint btn-transparent responsive-show"
                        class:active={showResponsiveControls}
                        use:tooltip={{ position: "top", text: "More options" }}
                        on:click={() => (showResponsiveControls = !showResponsiveControls)}
                    >
                        <i class="iconoir-more-vert" />
                    </button>
                </div>
            {/if}

            <div class="controls-group group-right responsive-hide">
                {#if $activeScreen?.id}
                    {#if link?.allowComments && $mode == modes.comments && !$isLoadingComments}
                        <button
                            type="button"
                            class="btn btn-sm btn-transparent btn-danger entrance-right"
                            on:click|preventDefault={() => commentsPanel?.toggle()}
                        >
                            <span class="txt">Comments panel</span>
                        </button>
                    {/if}

                    {#if $mode == modes.preview}
                        <ScreenPreviewOptionsBtn />
                    {/if}

                    {#if $activePrototype.scale != 0}
                        <button
                            class="btn btn-circle btn-hint btn-transparent {$fitToScreen
                                ? 'txt-warning'
                                : 'txt-hint'}"
                            use:tooltip={{ position: "top", text: "Toggle fit to screen" }}
                            on:click|preventDefault={toggleFitToScreen}
                        >
                            <i class="iconoir-scale-frame-reduce" />
                        </button>
                    {/if}
                {/if}

                {#if $prototypes.length > 1}
                    <div class="form-field form-field-sm">
                        <PrototypesSelect upside searchable={false} bind:value={$activePrototypeId} />
                    </div>
                {/if}

                <ProjectInfoBtn />
            </div>
        </nav>
    {/if}
</div>

<ScreensBar active={screensBarActive} />

<CommentPopover viewport={screenNavWrapper} />
