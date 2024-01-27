<script>
    import { link } from "svelte-spa-router";

    export let withText = true;

    let classes = "";
    export { classes as class }; // export reserved keyword
</script>

<a href="/" class="app-logo {classes}" draggable="false" use:link>
    <img src="{import.meta.env.BASE_URL}images/logo.svg" width="30" height="37" alt="Presentator logo" />
    {#if withText}
        <span class="txt">Presentator</span>
    {/if}
</a>
