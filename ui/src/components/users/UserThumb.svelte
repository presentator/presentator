<script>
    import pb from "@/pb";
    import utils from "@/utils";

    export let user;
    export let file = null; // File/Blob

    let classes = "";
    export { classes as class }; // export reserved keyword

    let url = "";

    $: if (file || user) {
        loadFileURL();
    }

    async function loadFileURL() {
        if (file) {
            url = await utils.generateThumb(file);
        } else if (user?.avatar) {
            url = pb.files.getURL(user, user.avatar, { thumb: "100x100" });
        } else {
            url = "";
        }
    }
</script>

<!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
<!-- svelte-ignore a11y-click-events-have-key-events -->
<figure class="thumb thumb-circle {classes}" {...$$restProps}>
    {#if url}
        <img src={url} draggable="false" alt="{user?.name || user?.username} avatar" />
    {:else}
        <slot name="placeholder">
            {#if user?.name || user?.email}
                <span class="txt">{utils.getInitials(user.name || user.email)}</span>
            {:else}
                <i class="iconoir-user" />
            {/if}
        </slot>
    {/if}

    <slot />
</figure>
