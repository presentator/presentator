<script>
    import utils from "@/utils";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import { prototypes } from "@/stores/prototypes";

    export let value;

    $: items = $prototypes.map((p) => {
        return {
            value: p.id,
            label: p.title,
            icon: utils.getPrototypeIcon(p),
        };
    });
</script>

<ObjectSelect
    searchable
    class="prototypes-select"
    dropdownClass="dropdown-right"
    noOptionsText="No prototypes found"
    selectPlaceholder="- Select prototype -"
    {items}
    bind:keyOfSelected={value}
    {...$$restProps}
/>

<style>
    :global(.prototypes-select) {
        min-width: 170px;
    }
</style>
