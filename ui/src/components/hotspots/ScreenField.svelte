<script>
    import Field from "@/components/base/Field.svelte";
    import { screens } from "@/stores/screens";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import ScreenOption from "@/components/hotspots/ScreenOption.svelte";
    import ScreenLabel from "@/components/hotspots/ScreenLabel.svelte";

    export let value;
    export let label = "Screen";

    let classes = "";
    export { classes as class }; // export reserved keyword

    $: if (!value && $screens.length) {
        value = $screens[0].id;
    }
</script>

<Field class="form-field required {classes}" name="settings.screen" let:uniqueId>
    <label for={uniqueId}>{label}</label>
    <ObjectSelect
        id={uniqueId}
        searchable
        class="screens-select"
        items={$screens}
        noOptionsText="No screens found"
        selectPlaceholder="- Select screen -"
        labelComponent={ScreenLabel}
        optionComponent={ScreenOption}
        selectionKey="id"
        bind:keyOfSelected={value}
        {...$$restProps}
    />
</Field>
