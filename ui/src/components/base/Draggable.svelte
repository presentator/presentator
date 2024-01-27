<script>
    import { createEventDispatcher } from "svelte";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";

    const dispatch = createEventDispatcher();

    export let disabled = false;
    export let tolerance = 6;
    export let passive = false; // whether to skip the element top/left position change on drag
    export let parentSelector = null; // default to first parent node

    let classes = undefined;
    export { classes as class }; // export reserved keyword

    let tooltipConfig = null;
    export { tooltipConfig as tooltip };

    let elem;
    let startX = 0;
    let startY = 0;
    let shiftX = 0;
    let shiftY = 0;
    let lockX = false;
    let lockY = false;
    let dragStarted = false;

    $: parent = elem?.closest(parentSelector) || elem?.parentNode;

    export function getElem() {
        return elem;
    }

    export function dragInit(e) {
        if (disabled || !elem || !e) {
            return;
        }

        e.stopPropagation();

        utils.normalizePointerEvent(e);

        startX = e.clientX;
        startY = e.clientY;
        shiftX = startX - elem.offsetLeft;
        shiftY = startY - elem.offsetTop;

        document.addEventListener("touchmove", onMove);
        document.addEventListener("mousemove", onMove);
        document.addEventListener("touchend", onStop);
        document.addEventListener("mouseup", onStop);

        dispatch("draginit", { event: e, elem });
    }

    function onStop(e) {
        utils.normalizePointerEvent(e);

        const dragged = dragStarted;

        parent?.classList?.remove("child-dragging");

        if (dragStarted) {
            // preventDefault is not allowed for passive touch events
            if (!e.touches) {
                e.preventDefault();
            }

            dragStarted = false;
            lockX = false;
            lockY = false;

            elem.classList.remove("no-pointer-events", "dragging");
            dispatch("dragstop", { event: e, elem });
        }

        document.removeEventListener("touchmove", onMove);
        document.removeEventListener("mousemove", onMove);
        document.removeEventListener("touchend", onStop);
        document.removeEventListener("mouseup", onStop);

        dispatch("dragrelease", { event: e, elem, dragged });
    }

    function onMove(e) {
        utils.normalizePointerEvent(e);

        let left = e.clientX - shiftX;
        let top = e.clientY - shiftY;

        if (
            !dragStarted &&
            Math.abs(left - elem.offsetLeft) < tolerance &&
            Math.abs(top - elem.offsetTop) < tolerance
        ) {
            return;
        }

        // preventDefault is not allowed for passive touch events
        if (!e.touches) {
            e.preventDefault();
        }

        let diffX = e.clientX - startX;
        let diffY = e.clientY - startY;

        const eventData = {
            event: e,
            elem: elem,
            diffX: diffX,
            diffY: diffY,
        };

        if (!dragStarted) {
            dragStarted = true;
            elem.classList.add("no-pointer-events", "dragging");
            parent.classList.add("child-dragging");
            dispatch("dragstart", eventData);
        }

        dispatch("dragging", eventData);

        if (passive) {
            return;
        }

        // lock/release axis
        if (e.shiftKey) {
            if (!lockX && !lockY) {
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    lockX = false;
                    lockY = true;
                } else {
                    lockX = true;
                    lockY = false;
                }
            }
        } else {
            lockX = false;
            lockY = false;
        }

        // Horizontal
        // ---
        if (!lockX) {
            // right side boundary
            let parentWidth = parent.scrollWidth || parent.offsetWidth;
            if (left + elem.offsetWidth > parentWidth) {
                left = parentWidth - elem.offsetWidth;
            }

            // left side boundary
            left = left < 0 ? 0 : left;

            elem.style.left = left + "px";
        }

        // Vertical
        // ---
        if (!lockY) {
            // bottom side boundary
            let parentHeight = parent.scrollHeight || parent.offsetHeight;
            if (top + elem.offsetHeight > parentHeight) {
                top = parentHeight - elem.offsetHeight;
            }

            // top side boundary
            top = top < 0 ? 0 : top;

            elem.style.top = top + "px";
        }
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div
    bind:this={elem}
    class="draggable {classes}"
    class:dragging={dragStarted}
    on:mousedown={(e) => {
        if (e.button == 0) {
            dragInit(e);
        }
    }}
    on:touchstart={dragInit}
    on:click
    on:keydown
    use:tooltip={tooltipConfig}
    {...$$restProps}
>
    <slot />
</div>
