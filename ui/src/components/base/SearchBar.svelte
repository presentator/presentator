<script>
    import { fly } from "svelte/transition";
    import utils from "@/utils";

    const uniqueId = "search_" + utils.randomString(5);

    const btnRelativeSize = {
        lg: "sm",
        md: "sm",
        sm: "xs",
    };

    let classes = "";
    export { classes as class }; // export reserved keyword

    export let value;
    export let placeholder = "Search...";
    export let size = "lg"; // eg. xs/sm/md/lg

    let searchInput;

    $: isEmpty = utils.isEmpty(value);

    function focus() {
        searchInput?.focus();
    }

    function clear() {
        value = "";
    }
</script>

<div class="form-field form-field-{size} search-bar {classes}">
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div class="field-group" on:click={focus}>
        <slot name="prefix" {isEmpty} />

        <div class="addon prefix">
            <i class="iconoir-search" />
        </div>

        <input bind:this={searchInput} type="text" id={uniqueId} {placeholder} bind:value />

        {#if !isEmpty}
            <!-- prettier-ignore -->
            <button
                type="button"
                class="btn btn-warning btn-{btnRelativeSize[size] || 'sm'} btn-expanded-{btnRelativeSize[size] || 'sm'}"
                on:click|stopPropagation={clear}
                transition:fly={{ duration: 150, x: 5 }}
            >
                <span class="txt">Clear</span>
            </button>
        {/if}

        <slot name="suffix" {isEmpty} />
    </div>
</div>
