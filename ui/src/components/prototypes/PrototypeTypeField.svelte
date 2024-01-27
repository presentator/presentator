<script>
    import Field from "@/components/base/Field.svelte";
    import SizesSelect from "@/components/prototypes/SizesSelect.svelte";

    export let prototype;

    const typeDesktop = "desktop";
    const typeMobile = "mobile";
    const sizeOptions = [
        { label: "Google Pixel", value: "412x732" },
        { label: "iPhone XS", value: "375x812" },
        { label: "iPhone 13/14", value: "390x844" },
        { label: "iPad Pro", value: "1024x1366" },
        { label: "Other", value: "" },
    ];

    let type = typeDesktop;
    let oldType = type;

    $: if (prototype) {
        refreshType();
    }

    // type change
    $: if (oldType != type) {
        oldType = type;

        if (type == typeDesktop) {
            resetDesktopMeta();
        } else {
            resetMobileMeta();
        }
    }

    function refreshType() {
        type = !prototype.size ? typeDesktop : typeMobile;
        oldType = type;

        // init default desktop scale
        if (type == typeDesktop && !prototype.scale) {
            prototype.scale = 1;
        }
    }

    function resetDesktopMeta() {
        prototype.size = "";
        prototype.scale = prototype.scale == 0.5 ? 0.5 : 1;
    }

    function resetMobileMeta() {
        prototype.size = sizeOptions[0].value;
        prototype.scale = 0;
    }
</script>

<Field class="form-field m-b-sm">
    <div class="radio-group type-group">
        <Field class="group-item" let:uniqueId>
            <input type="radio" id={uniqueId} name="type" bind:group={type} value={typeDesktop} />
            <label for={uniqueId}>
                <i class="iconoir-modern-tv" />
                <span class="txt">Desktop</span>
            </label>
        </Field>
        <Field class="group-item" let:uniqueId>
            <input type="radio" id={uniqueId} name="type" bind:group={type} value={typeMobile} />
            <label for={uniqueId}>
                <i class="iconoir-smartphone-device" />
                <span class="txt">Mobile</span>
            </label>
        </Field>
    </div>
</Field>

{#if type == typeDesktop}
    <Field class="form-field entrance-top" name="scale" let:uniqueId>
        <input
            type="checkbox"
            id={uniqueId}
            checked={prototype.scale == 0.5}
            on:change={(e) => {
                if (e?.target?.checked) {
                    prototype.scale = 0.5;
                } else {
                    prototype.scale = 1;
                }
            }}
        />
        <label for={uniqueId}>
            <span class="txt">2x Retina rescale</span>
        </label>
    </Field>
{:else}
    <Field class="form-field m-b-sm entrance-top" let:uniqueId>
        <SizesSelect id={uniqueId} options={sizeOptions} bind:value={prototype.size} />
    </Field>
    <Field class="form-field entrance-top" name="scale" let:uniqueId>
        <input
            type="checkbox"
            id={uniqueId}
            checked={prototype.scale == 0}
            on:change={(e) => {
                if (e?.target?.checked) {
                    prototype.scale = 0;
                } else {
                    prototype.scale = 1;
                }
            }}
        />
        <label for={uniqueId}>
            <span class="txt">Auto scale</span>
        </label>
    </Field>
{/if}

<style lang="scss">
    .type-group label {
        padding: 10px;
        flex-direction: column;
        i {
            font-size: 120%;
        }
    }
</style>
