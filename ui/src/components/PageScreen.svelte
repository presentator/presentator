<script>
    import { onDestroy } from "svelte";
    import { push, querystring } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import { resetProjectsStore, addProject, activeProject } from "@/stores/projects";
    import { resetPrototypesStore, addPrototype, activePrototype } from "@/stores/prototypes";
    import {
        activeScreen,
        screens,
        resetScreensStore,
        changeActiveScreenByOffset,
        mode,
        changeMode,
        fitToScreen,
        loadModeFromUrl,
        modes,
        getScreenOrder,
        initScreensSubscription,
        unsubscribeScreensFunc,
        toggleFitToScreen,
        loadFitToScreen,
        replaceScreenWithConfirm,
    } from "@/stores/screens";
    import {
        loadComments,
        isLoadingComments,
        selectedComment,
        activeScreenUnresolvedPrimaryComments,
        unsubscribeCommentsFunc,
        initCommentsSubscription,
    } from "@/stores/comments";
    import {
        loadHotspots,
        isLoadingHotspots,
        selectedHotspot,
        initHotspotsSubscription,
        unsubscribeHotspotsFunc,
    } from "@/stores/hotspots";
    import {
        loadTemplates,
        isLoadingTemplates,
        initTemplatesSubscription,
        unsubscribeHotspotTemplatesFunc,
    } from "@/stores/templates";
    import { activeScreenNotifications } from "@/stores/notifications";
    import Layout from "@/components/base/Layout.svelte";
    import ScreensBar from "@/components/screens/ScreensBar.svelte";
    import ScreenPreview from "@/components/screens/ScreenPreview.svelte";
    import ScreenSettingsBtn from "@/components/screens/ScreenSettingsBtn.svelte";
    import ScreenTemplatesBtn from "@/components/screens/ScreenTemplatesBtn.svelte";
    import ScreenPreviewOptionsBtn from "@/components/screens/ScreenPreviewOptionsBtn.svelte";
    import CommentsPanel from "@/components/comments/CommentsPanel.svelte";
    import CommentPopover from "@/components/comments/CommentPopover.svelte";
    import HotspotPopover from "@/components/hotspots/HotspotPopover.svelte";

    const initialQueryParams = utils.getHashQueryParams();

    export let params;

    let pageTitle = "";
    let isLoading = false;
    let showScreensBar = false;
    let showResponsiveControls = false;
    let commentsPanel;
    let screenNavWrapper;
    let latestActiveScreenId = params.screenId;

    loadModeFromUrl();
    loadFitToScreen();

    load(params.prototypeId, params.screenId);

    $: pageTitle = [$activeScreen?.title, $activePrototype?.title, $activeProject?.title]
        .filter(Boolean)
        .join(" - ");

    // handle back browser button navigation
    $: if (
        $activeScreen &&
        latestActiveScreenId == $activeScreen.id &&
        params.screenId != latestActiveScreenId
    ) {
        latestActiveScreenId = params.screenId;
        $activeScreen = params.screenId;
    }

    // update url on screen change
    $: if ($activeScreen && $activeScreen.id != latestActiveScreenId) {
        latestActiveScreenId = $activeScreen.id;
        push(
            `/projects/${$activePrototype.project}/prototypes/${$activePrototype.id}/screens/${$activeScreen.id}?${$querystring}`,
        );
    }

    $: isLoaded = !isLoading && !!$activeProject?.id && !!$activePrototype?.id && !!$activeScreen?.id;

    $: activeScreenIndex = $screens.findIndex((v) => v.id == $activeScreen?.id);

    $: isFirstScreen = activeScreenIndex === 0;

    $: isLastScreen = $screens.length - 1 === activeScreenIndex;

    $: utils.replaceHashQueryParams({ commentId: $selectedComment?.id || null });

    async function load(prototypeId, screenId) {
        if (!prototypeId || !screenId) {
            return;
        }

        resetProjectsStore();
        resetPrototypesStore();
        resetScreensStore();

        isLoading = true;

        try {
            initCommentsSubscription(prototypeId);
            initHotspotsSubscription(prototypeId);
            initTemplatesSubscription(prototypeId);
            initScreensSubscription(prototypeId);

            loadTemplates(prototypeId);
            loadHotspots(prototypeId);

            const prototype = await pb.collection("prototypes").getOne(prototypeId, {
                expand: "project.users,screens_via_prototype",
            });

            addPrototype(prototype, true);

            addProject(prototype.expand.project, true);

            $activeScreen = screenId;
            $screens = utils.sortItemsByIds(prototype.expand.screens_via_prototype, prototype.screensOrder);

            // note: the comments should be loaded after the screens
            // to avoid races and complicated state checks
            loadComments(prototypeId, initialQueryParams.commentId);

            isLoading = false;
        } catch (err) {
            if (!err.isAbort) {
                isLoading = false;
                pb.error(err);
            }
        }
    }

    function goBack() {
        push(`/projects/${params?.projectId}/prototypes/${params.prototypeId}`);
    }

    function toggleScreensBar() {
        showScreensBar = !showScreensBar;
    }

    const excludeNavSelectors = ".toggler-container > .active";

    function onKeydownNav(e) {
        if (e.altKey || e.ctrlKey || utils.isInput(e.target) || document.querySelector(excludeNavSelectors)) {
            return;
        }

        if (e.code === "Escape" && showScreensBar) {
            e.preventDefault();
            showScreensBar = false;
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

        if (e.code === "KeyH") {
            e.preventDefault();
            changeMode(modes.hotspots);
            return;
        }

        if (e.code === "KeyC") {
            e.preventDefault();
            changeMode(modes.comments);
            return;
        }
    }

    onDestroy(() => {
        unsubscribeScreensFunc?.();
        unsubscribeCommentsFunc?.();
        unsubscribeHotspotsFunc?.();
        unsubscribeHotspotTemplatesFunc?.();
    });
</script>

<svelte:window on:keydown={onKeydownNav} />

<Layout
    fullpage
    class="screen-preview-layout screen-preview-mode-{$mode} {$mode == modes.comments && $selectedComment
        ? 'screen-preview-comment-popover-active'
        : ''} {$mode == modes.hotspots && $selectedHotspot ? 'screen-preview-hotspot-popover-active' : ''}"
    header={false}
    footer={false}
    title={pageTitle}
>
    {#if !isLoaded}
        <span class="loader loader-lg m-t-auto m-b-auto" />
    {:else}
        <div class="screen-preview-container entrance-fade">
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <div
                bind:this={screenNavWrapper}
                class="screen-preview-nav-wrapper"
                on:drop|preventDefault={(e) => {
                    if (e.dataTransfer.files.length) {
                        replaceScreenWithConfirm($activeScreen, e.dataTransfer.files[0]);
                    }
                }}
                on:dragover|preventDefault|stopPropagation
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

        <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
        <!-- svelte-ignore a11y-click-events-have-key-events -->
        <nav
            class="controls-bar"
            class:show-responsive-controls={showResponsiveControls}
            on:click|preventDefault={() => {
                document.activeElement.blur(); // clears the focus from the btn
            }}
        >
            <div class="controls-group group-left">
                <button
                    type="button"
                    class="btn btn-circle btn-transparent btn-hint txt-hint"
                    on:click={goBack}
                    use:tooltip={{ position: "top", text: "Back to listing" }}
                >
                    <i class="iconoir-xmark" />
                </button>

                <button
                    type="button"
                    class="btn btn-sm btn-hint txt-hint {showScreensBar
                        ? 'btn-semitransparent'
                        : 'btn-transparent'}"
                    on:click|preventDefault={toggleScreensBar}
                >
                    <span class="screen-title-trim" title={$activeScreen?.title}>
                        {$activeScreen?.title}
                    </span>
                    ({getScreenOrder($activeScreen)} of {$screens.length})
                    <i class="iconoir-nav-arrow-{showScreensBar ? 'up' : 'down'}" />
                </button>
            </div>

            <div class="controls-group group-center">
                <button
                    class="btn btn-circle {$mode == modes.preview
                        ? 'btn-primary btn-transparent'
                        : 'btn-hint btn-transparent txt-hint'}"
                    use:tooltip={{ position: "top", text: "Preview mode", sub: "P" }}
                    on:click|preventDefault={() => changeMode(modes.preview)}
                >
                    <i class="iconoir-play" />
                </button>
                <button
                    class="btn btn-circle {$mode == modes.hotspots
                        ? 'btn-success btn-transparent'
                        : 'btn-hint btn-transparent txt-hint'}"
                    class:btn-loading={$isLoadingTemplates || $isLoadingHotspots}
                    use:tooltip={{ position: "top", text: "Hotspots mode", sub: "H" }}
                    on:click|preventDefault={() => changeMode(modes.hotspots)}
                >
                    <i class="iconoir-square-3d-corner-to-corner" />
                </button>
                <button
                    class="btn btn-circle {$mode == modes.comments
                        ? 'btn-danger btn-transparent'
                        : 'btn-hint btn-transparent txt-hint'}"
                    class:btn-loading={$isLoadingComments}
                    use:tooltip={{ position: "top", text: "Comments mode", sub: "C" }}
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
                            title="Unresolved comments">{$activeScreenUnresolvedPrimaryComments.length}</sub
                        >
                    {/if}
                </button>
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

            <div class="controls-group group-right responsive-hide">
                {#if $mode == modes.hotspots && !$isLoadingTemplates}
                    <ScreenTemplatesBtn />
                {/if}

                {#if $mode == modes.comments && !$isLoadingComments}
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

                {#if $activePrototype?.scale != 0}
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

                <ScreenSettingsBtn />
            </div>
        </nav>

        <ScreensBar active={showScreensBar} />
    {/if}
</Layout>

<CommentPopover viewport={screenNavWrapper} />
<HotspotPopover viewport={screenNavWrapper} />
