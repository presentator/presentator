<script>
    import { onDestroy } from "svelte";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";

    export let value = "";
    export let idleClasses = "iconoir-copy link-hint";
    export let successClasses = "iconoir-check txt-success";
    export let successDuration = 500; // ms

    let classes = "";
    export { classes as class }; // export reserved keyword

    let copyTimeout;

    function copy() {
        if (!value) {
            return;
        }

        utils.copyToClipboard(value);

        clearTimeout(copyTimeout);
        copyTimeout = setTimeout(() => {
            clearTimeout(copyTimeout);
            copyTimeout = null;
        }, successDuration);
    }

    onDestroy(() => {
        if (copyTimeout) {
            clearTimeout(copyTimeout);
        }
    });
</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<i
    class="{classes} {copyTimeout ? successClasses : idleClasses}"
    aria-label={"Copy"}
    use:tooltip={!copyTimeout ? "Copy" : ""}
    on:click|stopPropagation={copy}
/>
