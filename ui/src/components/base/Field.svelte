<script>
    import { onMount } from "svelte";
    import { slide } from "svelte/transition";
    import tooltip from "@/actions/tooltip";
    import { errors, removeError } from "@/stores/errors";
    import utils from "@/utils";

    const uniqueId = "field_" + utils.randomString(7);
    const defaultError = "Invalid value";

    export let name = "";
    export let inlineError = false;

    let classes = undefined;
    export { classes as class }; // export reserved keyword

    let container;
    let fieldErrors = [];

    $: fieldErrors = utils.toArray(utils.getNestedVal($errors, name));

    export function changed() {
        removeError(name);
    }

    function getErrorMessage(err) {
        if (!err) {
            return "";
        }

        if (typeof err === "object") {
            return err?.message || err?.code || defaultError;
        }

        return err || defaultError;
    }

    onMount(() => {
        container.addEventListener("input", changed);
        container.addEventListener("change", changed);

        return () => {
            container.removeEventListener("input", changed);
            container.removeEventListener("change", changed);
        };
    });
</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<div
    bind:this={container}
    class={classes}
    class:error={fieldErrors.length}
    use:tooltip={{ text: inlineError ? getErrorMessage(fieldErrors?.[0]) : "" }}
    on:click
>
    <slot {uniqueId} />

    {#if !inlineError}
        {#each fieldErrors as error}
            <div class="help-block help-block-error" transition:slide={{ duration: 150 }}>
                <pre>{getErrorMessage(error)}</pre>
            </div>
        {/each}
    {/if}
</div>
