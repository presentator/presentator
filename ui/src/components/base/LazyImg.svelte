<script>
    import { createEventDispatcher } from "svelte";

    const dispatch = createEventDispatcher();

    export let src;
    export let alt = undefined;
    export let width = undefined;
    export let height = undefined;
    export let fetchpriority = undefined;
    export let draggable = null;
    export let scale = undefined;
    export let loading = "lazy";
    export let loaderClass = "loader";
    export let isLoading = true;

    let classes = "";
    export { classes as class }; // export reserved keyword

    let img;
    let oldSrc = src;

    $: if (oldSrc != src) {
        oldSrc = src;
        isLoading = true;
    }

    $: if (typeof scale !== "undefined") {
        rescale();
    }

    export function widthRatio() {
        return img?.naturalWidth ? img.clientWidth / img.naturalWidth : 1;
    }

    function onLoad() {
        rescale();
        dispatch("load", img);
        isLoading = false;
    }

    function onError() {
        isLoading = false;
        dispatch("error", img);
    }

    function rescale() {
        if (!img || !img.complete || typeof scale === "undefined") {
            return;
        }

        // reset
        img.style.width = "auto";
        img.style.height = "auto";

        if (scale == 0) {
            img.style["max-width"] = "100%";
        } else {
            img.style["max-width"] = "";
        }

        const width = scale > 0 ? img.naturalWidth * scale : img.naturalWidth;

        img.style.width = width + "px";
    }
</script>

<figure class="lazy-load {classes}" class:loading={isLoading}>
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
    <img
        bind:this={img}
        crossorigin="anonymous"
        {loading}
        {fetchpriority}
        {alt}
        {width}
        {height}
        {draggable}
        {src}
        on:load={onLoad}
        on:error={onError}
        on:click
        on:mousedown
        on:touchstart
    />

    {#if isLoading}
        <span class={loaderClass} />
    {/if}

    <slot {isLoading} {img} />
</figure>
