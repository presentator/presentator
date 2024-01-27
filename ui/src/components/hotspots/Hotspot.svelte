<script context="module">
    let transitionStartTimeout;
    let transitionEndTimeout;
</script>

<script>
    import { tick } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import Draggable from "@/components/base/Draggable.svelte";
    import { options } from "@/stores/app";
    import {
        selectedHotspot,
        isHotspotDragging,
        hotspotTypes,
        addHotspot,
        removeHotspot,
        removeUnsavedHotspots,
    } from "@/stores/hotspots";
    import {
        screens,
        activeScale,
        transition,
        activeScreen,
        transitionScreen,
        prevActiveScreen,
        overlayScreenSettings,
        getScreenByActiveOffset,
    } from "@/stores/screens";

    const wrapperSelector = ".active-screen-preview-wrapper";
    const imgToSnapSelector = wrapperSelector + " .screen-preview-img img";
    const scrollContainerSelector = ".screen-preview";
    const minSize = 25;

    export let hotspot;
    export let preview = false;
    export let scale = null; // optional hotspot scale (fallback to $activeScale)
    export let backFunc = null; // optional custom handler for hotspotTypes.back (fallback to $prevActiveScreen)

    let container;
    let parent;
    let resizeHandle;
    let initialWidth = 0;
    let initialHeight = 0;
    let initialLeft = 0;
    let initialTop = 0;
    let isSaving = false;
    let tooltipOptions = undefined;

    $: hotspotScale = scale === null ? $activeScale : scale;

    $: if (hotspot.startResizing && resizeHandle) {
        resizeHandle.dragInit(hotspot.startResizing);
        delete hotspot.startResizing;
    }

    $: elem = container?.getElem();

    $: if (wrapperSelector) {
        getWrapper(true);
    }

    $: disabled = $isHotspotDragging || ($selectedHotspot && $selectedHotspot.id != hotspot.id);

    $: if (hotspot || preview) {
        refreshTooltipOptions();
    }

    function refreshTooltipOptions() {
        tooltipOptions = undefined;

        if ($selectedHotspot?.id == hotspot.id) {
            return; // no tooltip for active hotspots
        }

        if (hotspot.type == hotspotTypes.note) {
            tooltipOptions = { position: "follow", text: hotspot.settings?.note, hideOnClick: false };
        } else if (!preview) {
            tooltipOptions = {
                position: "top",
                text: "Ctrl + Click to follow\nShift + Click to duplicate",
                delay: 500,
                hideOnClick: true,
            };
        }
    }

    function getWrapper(reset = false) {
        if (!parent || reset) {
            parent = document.querySelector(wrapperSelector);
        }
        return parent;
    }

    function syncHotspotWithElem() {
        if (!elem) {
            return;
        }

        Object.assign(hotspot, {
            left: elem.offsetLeft / hotspotScale,
            top: elem.offsetTop / hotspotScale,
            width: elem.offsetWidth / hotspotScale,
            height: elem.offsetHeight / hotspotScale,
        });
    }

    async function savePosition(e) {
        if (!elem) {
            return;
        }

        if (e?.detail?.event?.altKey) {
            await snap();
        }

        syncHotspotWithElem();

        // upsert store hotspot
        addHotspot(hotspot);

        if (!hotspot.id) {
            return; // new hotspot, not created yet
        }

        isSaving = true;

        try {
            const data = {
                left: hotspot.left,
                top: hotspot.top,
                width: hotspot.width,
                height: hotspot.height,
            };

            await pb.collection("hotspots").update(hotspot.id, data);

            isSaving = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSaving = false;
            }
        }
    }

    // "magnet" snap
    async function snap() {
        const imgToSnap = document.querySelector(imgToSnapSelector);
        if (!imgToSnap) {
            return;
        }

        syncHotspotWithElem();

        const closestEdge = utils.closestFeatureEdge(imgToSnap, {
            x: hotspot.left,
            y: hotspot.top,
            w: hotspot.width,
            h: hotspot.height,
        });

        hotspot.left += closestEdge.x;
        hotspot.top += closestEdge.y;
        hotspot.width = closestEdge.w;
        hotspot.height = closestEdge.h;

        // sync elem with hotspot
        await tick();
    }

    async function duplicate() {
        const wrapperWidth = getWrapper().offsetWidth || Infinity;

        const clone = structuredClone(hotspot);
        clone.id = "hcopy" + utils.randomString(10);
        clone.left = clone.left + clone.width;
        clone.disabled = true;
        if (clone.left + clone.width > wrapperWidth) {
            clone.left = wrapperWidth - clone.width;
        }

        // eager add the hotspot
        addHotspot(clone);

        try {
            // create and update its position
            addHotspot(await pb.collection("hotspots").create(clone));
            isSaving = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSaving = false;
                removeHotspot(clone);
            }
        }
    }

    // navigation
    // ---------------------------------------------------------------
    function navigate() {
        switch (hotspot.type) {
            case hotspotTypes.screen:
                return navigateScreen();
            case hotspotTypes.overlay:
                return navigateOverlay();
            case hotspotTypes.back:
                return navigateBack();
            case hotspotTypes.prev:
                return navigatePrev();
            case hotspotTypes.next:
                return navigateNext();
            case hotspotTypes.url:
                return navigateUrl();
            case hotspotTypes.scroll:
                return navigateScroll();
        }
    }

    function navigateScreen() {
        const screen = $screens.find((s) => s.id == hotspot.settings.screen);
        changeScreen(screen);
    }

    function navigateOverlay() {
        $overlayScreenSettings = hotspot.settings;
    }

    function navigateBack() {
        if (backFunc) {
            backFunc();
        } else if ($prevActiveScreen?.id) {
            changeScreen($prevActiveScreen);
        }
    }

    function navigatePrev() {
        const screen = getScreenByActiveOffset(-1);
        changeScreen(screen);
    }

    function navigateNext() {
        const screen = getScreenByActiveOffset(1);
        changeScreen(screen);
    }

    function navigateUrl() {
        if ($options?.allowHotspotsUrl) {
            window.open(hotspot.settings.url || "#", "_blank", "noopener,noreferrer");
        }
    }

    function navigateScroll() {
        document.querySelector(scrollContainerSelector)?.scrollTo({
            behavior: "smooth",
            top: hotspot.settings.scrollTop || 0,
            left: hotspot.settings.scrollLeft || 0,
        });
    }

    function cleanup() {
        clearTimeout(transitionEndTimeout);
        clearTimeout(transitionStartTimeout);
        $transitionScreen = null;
        $transition = "";
    }

    function changeScreen(screen) {
        if (!screen) {
            cleanup();
            return;
        }

        // no transition - direct replace
        if (!hotspot.settings.transition) {
            cleanup();
            $activeScreen = screen.id;
            return;
        }

        $transition = hotspot.settings.transition;
        $transitionScreen = screen.id;

        clearTimeout(transitionStartTimeout);
        transitionStartTimeout = setTimeout(() => {
            $activeScreen = $transitionScreen?.id;

            // short delay before clearing the transition to minimize the flickering
            clearTimeout(transitionEndTimeout);
            transitionEndTimeout = setTimeout(() => {
                $transition = "";
                $transitionScreen = "";
            }, 100);
        }, 350);
    }
</script>

<Draggable
    bind:this={container}
    class="hotspot type-{hotspot.type} {preview ? 'preview' : ''} {$selectedHotspot?.id == hotspot.id
        ? 'active'
        : ''} {disabled ? 'no-pointer-events' : ''} {hotspot.hotspotTemplate ? 'template' : ''}"
    data-hotspot={hotspot.id}
    parentSelector={wrapperSelector}
    disabled={disabled || preview || hotspot.disabled}
    style="
        left: min(calc(100% - {hotspot.width * hotspotScale}px), {hotspot.left * hotspotScale}px);
        top: min(calc(100% - {hotspot.height * hotspotScale}px), {hotspot.top * hotspotScale}px);
        width: {hotspot.width * hotspotScale}px;
        height: {hotspot.height * hotspotScale}px;
    "
    tooltip={tooltipOptions}
    on:dragstart={() => {
        $isHotspotDragging = true;
    }}
    on:dragrelease={() => {
        $isHotspotDragging = false;
    }}
    on:dragstop={savePosition}
    on:click={(e) => {
        e.stopImmediatePropagation();

        if ($isHotspotDragging) {
            return;
        }

        if (!preview && hotspot?.id && e.shiftKey) {
            return duplicate();
        }

        if ($selectedHotspot != hotspot) {
            removeUnsavedHotspots();
        }

        if (preview || e.ctrlKey) {
            navigate();
        } else {
            $selectedHotspot = hotspot;
        }
    }}
>
    {#if !preview}
        <Draggable
            bind:this={resizeHandle}
            class="resize-handle"
            passive
            parentSelector=".screen-preview-img-wrapper"
            on:draginit={() => {
                $isHotspotDragging = true;
            }}
            on:dragstart={() => {
                if (!elem) {
                    return;
                }

                initialWidth = elem.offsetWidth;
                initialHeight = elem.offsetHeight;
                initialLeft = elem.offsetLeft;
                initialTop = elem.offsetTop;
            }}
            on:dragstop={(e) => {
                savePosition(e);
                $isHotspotDragging = false;
            }}
            on:dragrelease={(e) => {
                // resize without drag
                if (!e.detail.dragged) {
                    $isHotspotDragging = false;

                    // select the hotspot if not already
                    if ($selectedHotspot?.id != hotspot?.id) {
                        $selectedHotspot = hotspot;
                    }
                }
            }}
            on:dragging={(e) => {
                if (!elem) {
                    return;
                }

                let width = initialWidth + e.detail.diffX;
                let height = initialHeight + e.detail.diffY;
                let left = initialLeft;
                let top = initialTop;

                const wrapperWidth = getWrapper().offsetWidth;
                const wrapperHeight = getWrapper().offsetHeight;

                // reverse horizontal resize
                if (width < 0) {
                    width = -1 * e.detail.diffX;
                    left = initialLeft + e.detail.diffX;
                    if (left < 0) {
                        left = 0;
                        width = initialLeft;
                    }
                }

                // restrict width to the right edge
                if (left + width >= wrapperWidth) {
                    width = wrapperWidth - left;
                    if (width < minSize) {
                        width = minSize;
                        left = wrapperWidth - width;
                    }
                }

                // reverse vertical resize
                if (height < 0) {
                    height = -1 * e.detail.diffY;
                    top = initialTop + e.detail.diffY;
                    if (top < 0) {
                        top = 0;
                        height = initialTop;
                    }
                }

                // restrict width to the bottom edge
                if (top + height >= wrapperHeight) {
                    height = wrapperHeight - top;
                    if (height < minSize) {
                        height = minSize;
                        top = wrapperHeight - height;
                    }
                }

                hotspot.width = width / hotspotScale;
                hotspot.height = height / hotspotScale;
                hotspot.left = left / hotspotScale;
                hotspot.top = top / hotspotScale;
            }}
        />
    {/if}
</Draggable>
