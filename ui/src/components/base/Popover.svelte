<script context="module">
    let holder;

    function getHolder() {
        holder = holder || document.querySelector(".popovers");

        if (!holder) {
            // create
            holder = document.createElement("div");
            holder.classList.add("popovers");
            document.body.appendChild(holder);
        }

        return holder;
    }
</script>

<script>
    import { onMount, createEventDispatcher, tick } from "svelte";
    import Toggler from "@/components/base/Toggler.svelte";

    const dispatch = createEventDispatcher();

    export let active = false;
    export let vPadding = 0;
    export let hPadding = 0;

    let classes = undefined;
    export { classes as class }; // export reserved keyword

    let popover;
    let toggler;
    let trigger;
    let viewport;
    let observer;
    let refreshPositionTimeoutId;

    export async function show(showTrigger, showViewport = null) {
        trigger = showTrigger;
        viewport = showViewport || document.body;

        toggler?.show();
    }

    export function hide() {
        toggler?.hide();
    }

    export function forceHide() {
        toggler?.forceHide();
    }

    export function throttleRefreshPosition(throttle = 100) {
        if (refreshPositionTimeoutId) {
            return;
        }

        refreshPositionTimeoutId = setTimeout(() => {
            refreshPositionTimeoutId = null;
            refreshPosition();
        }, throttle);
    }

    export function refreshPosition() {
        if (!active || !trigger || !popover) {
            return;
        }

        const popoverWidth = popover.offsetWidth;
        const popoverHeight = popover.offsetHeight;

        const viewportElem = viewport || document.body;
        const viewportWidth = viewportElem.clientWidth;
        const viewportHeight = viewportElem.clientHeight;

        let newTop = 0;
        let newLeft = 0;

        const triggerRect = trigger.getBoundingClientRect();

        // vertical position
        newTop = triggerRect.top + vPadding;

        // horizontal position
        const remainingWidth = viewportWidth - (triggerRect.left + triggerRect.width + hPadding);
        if (remainingWidth >= popoverWidth) {
            // right
            newLeft = triggerRect.left + triggerRect.width + hPadding;
        } else if (triggerRect.left - hPadding >= popoverWidth) {
            // left
            newLeft = triggerRect.left - hPadding - popoverWidth;
        } else {
            // center and below the trigger
            newLeft = triggerRect.left + triggerRect.width / 2 - popoverWidth / 2 - hPadding;

            newTop = triggerRect.top + triggerRect.height + vPadding;
        }

        // viewport boundaries normalization
        // ---
        if (newLeft + popoverWidth > viewportWidth) {
            newLeft = viewportWidth - popoverWidth;
        }
        newLeft = newLeft >= 0 ? newLeft : 0;

        if (newTop + popoverHeight > viewportHeight) {
            newTop = viewportHeight - popoverHeight;
        }
        newTop = newTop >= 0 ? newTop : 0;
        // ---

        popover.style.top = (newTop << 0) + "px";
        popover.style.left = (newLeft << 0) + "px";
    }

    async function onShow() {
        resetRefreshThrottle();

        // ensures that that the popover content has been rendered
        await tick();

        refreshPosition();

        initObserver();

        dispatch("show");
    }

    function onHide() {
        resetRefreshThrottle();

        disconnectObserver();

        dispatch("hide");
    }

    function initObserver() {
        disconnectObserver();

        if (!trigger || !popover) {
            return;
        }

        observer = new MutationObserver(() => {
            throttleRefreshPosition(50);
        });

        observer.observe(trigger, {
            attributeFilter: ["width", "height", "style", "display"],
            childList: true,
            subtree: true,
        });

        observer.observe(popover, {
            attributeFilter: ["width", "height"],
            childList: true,
            subtree: true,
        });
    }

    function disconnectObserver() {
        observer?.disconnect();
        observer = null;
    }

    function resetRefreshThrottle() {
        clearTimeout(refreshPositionTimeoutId);
        refreshPositionTimeoutId = null;
    }

    onMount(() => {
        // move outside of its current parent
        getHolder().appendChild(popover);

        refreshPosition();

        const scrollHandler = () => {
            throttleRefreshPosition(100);
        };

        document.body.addEventListener("scroll", scrollHandler, true);

        return () => {
            document.body.removeEventListener("scroll", scrollHandler, true);
            resetRefreshThrottle();
            disconnectObserver();
            popover?.remove();
        };
    });
</script>

<svelte:window on:resize={refreshPosition} on:mainScreenLoaded={refreshPosition} />

<div bind:this={popover} class="popover-container" class:active>
    <Toggler
        bind:this={toggler}
        class="popover {classes}"
        {trigger}
        bind:active
        on:show={onShow}
        on:hide={onHide}
        {...$$restProps}
    >
        <slot />
    </Toggler>
</div>
