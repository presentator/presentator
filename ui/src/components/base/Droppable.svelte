<script>
    /**
     * Example usage:
     * ```html
     * <Droppable on:drop={(e) => console.log(e.detail) } let:dragover>
     *     <div class:dragover={dragover}>...</div>
     * </Droppable>
     * ```
     */

    import { createEventDispatcher } from "svelte";

    const dispatch = createEventDispatcher();

    let dragover = false;
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<div
    on:dragover|preventDefault|stopPropagation
    on:dragenter|preventDefault|stopPropagation={(e) => {
        dragover = true;
        dispatch("dragenter");
    }}
    on:dragleave|preventDefault={(e) => {
        // prevent misfiring on child hover
        if (e.currentTarget.contains(e.relatedTarget)) {
            return;
        }

        e.stopPropagation();
        dragover = false;
        dispatch("dragleave");
    }}
    on:drop|preventDefault|stopPropagation={(e) => {
        dragover = false;

        if (e.dataTransfer.files.length) {
            dispatch("drop", e.dataTransfer.files);
        }
    }}
>
    <slot {dragover} />
</div>
