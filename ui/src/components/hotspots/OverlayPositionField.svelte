<script>
    import Field from "@/components/base/Field.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";

    const options = [
        { value: "top-left", label: "Top Left", icon: "iconoir-border-tl" },
        { value: "top-right", label: "Top Right", icon: "iconoir-border-tr" },
        { value: "bottom-left", label: "Bottom Left", icon: "iconoir-border-bl" },
        { value: "bottom-right", label: "Bottom Right", icon: "iconoir-border-br" },
        { value: "top-center", label: "Top Center", icon: "iconoir-border-top" },
        { value: "bottom-center", label: "Bottom Center", icon: "iconoir-border-bottom" },
        { value: "centered", label: "Centered", icon: "iconoir-border-inner" },
    ];

    export let value;
    export let label = "Position";

    let classes = undefined;
    export { classes as class }; // export reserved keyword

    // normalize option value
    $: if (!value) {
        value = "centered";
    }
</script>

<Field class="form-field required {classes}" name="settings.overlayPosition" let:uniqueId>
    <label for={uniqueId}>{label}</label>
    <ObjectSelect class="duo-columns-dropdown" id={uniqueId} items={options} bind:keyOfSelected={value} />
</Field>
