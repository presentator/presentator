<script>
    import Field from "@/components/base/Field.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";

    const options = [
        { value: "", label: "None", icon: "iconoir-position-align" },
        { value: "fade", label: "Fade", icon: "iconoir-center-align" },
        { value: "slide-left", label: "Slide left", icon: "iconoir-align-left-box" },
        { value: "slide-right", label: "Slide right", icon: "iconoir-align-right-box" },
        { value: "slide-top", label: "Slide up", icon: "iconoir-align-top-box" },
        { value: "slide-bottom", label: "Slide down", icon: "iconoir-align-bottom-box" },
    ];

    export let value = "";
    export let label = "Transitions";

    let classes = "";
    export { classes as class }; // export reserved keyword

    // normalize option value
    $: if (typeof value === "undefined" || value === null) {
        value = "";
    }
</script>

<Field class="form-field {classes}" name="settings.transition" let:uniqueId>
    <label for={uniqueId}>{label}</label>
    <ObjectSelect class="duo-columns-dropdown" id={uniqueId} items={options} bind:keyOfSelected={value} />
</Field>
