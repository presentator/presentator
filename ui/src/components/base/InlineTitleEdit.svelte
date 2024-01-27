<script>
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";

    export let collection = "";
    export let model = {};
    export let disabled = false;
    export let tag = "div";
    export let prop = "title";
    export let placeholder = "Title";

    let tooltipText = "Edit title";
    export { tooltipText as tooltip };

    let classes = "";
    export { classes as class };

    let elem;
    let focused = false;

    async function save() {
        if (!elem) {
            return;
        }

        const content = elem.textContent;

        if (!content) {
            elem.textContent = model[prop];
        }

        if (content == model[prop]) {
            return; // no change
        }

        try {
            const data = {};
            data[prop] = content;

            const updated = await pb
                .collection(collection)
                .update(model.id, data, { requestKey: "inline_" + collection + model.id });

            model[prop] = updated[prop];
            model.updated = updated.updated;
        } catch (err) {
            if (!err.isAbort) {
                console.warn("Failed to update " + prop, err);
            }
        }
    }
</script>

<div class="inline-edit-container">
    <!--
        note: enable the contenteditable attribute only when focused because
        chrome seems to focus contenteditable elements even when clicking somewhere near them
        (I guess for accessibility reason?)
    -->
    <svelte:element
        this={tag}
        bind:this={elem}
        spellcheck={false}
        tabindex="0"
        contenteditable={focused && !disabled}
        data-placeholder={placeholder}
        class="contenteditable inline-edit {classes}"
        on:focus={() => (focused = true)}
        on:blur={() => {
            focused = false;
            save();
        }}
        on:keypress={(e) => {
            if (e.code == "Enter") {
                e.preventDefault();
                elem?.blur();
            }
        }}
        use:tooltip={!focused ? { position: "bottom-left", text: tooltipText } : ""}
        {...$$restProps}
    >
        {model[prop]}
    </svelte:element>
</div>

<style lang="scss">
    .inline-edit-container {
        margin-right: auto;
        max-width: 100%;
        min-width: 0;
    }
</style>
