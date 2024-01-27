<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";
    import PrototypeTypeField from "@/components/prototypes/PrototypeTypeField.svelte";

    const formId = "prototype_" + utils.randomString(5);

    const dispatch = createEventDispatcher();

    let panel;
    let prototype = {};
    let isSubmitting = false;
    let isTitleDirty = false;

    // set default initial title based on the selected type
    $: if (!isTitleDirty && !prototype.id) {
        prototype.title = !prototype.size ? "Desktop" : "Mobile";
    }

    export function show(prototypeData) {
        reset();

        prototype = Object.assign({}, prototypeData);

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    function reset() {
        setErrors({});
        isTitleDirty = false;
    }

    async function submit() {
        if (!prototype.project || isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            let result;
            if (prototype.id) {
                result = await pb.collection("prototypes").update(prototype.id, prototype);

                addSuccessToast(`Successfully updated prototype ${prototype.title}.`);
            } else {
                result = await pb.collection("prototypes").create(prototype);

                addSuccessToast(`Successfully created prototype ${prototype.title}.`);
            }

            isSubmitting = false;
            hide();
            dispatch("submit", result);
        } catch (err) {
            if (!err.isAbort) {
                isSubmitting = false;
                pb.error(err);
            }
        }
    }
</script>

<OverlayPanel
    bind:this={panel}
    popup
    overlayClose={!isSubmitting}
    escHide={!isSubmitting}
    beforeHide={() => !isSubmitting}
    on:show
    on:hide
>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">
            {prototype.id ? "Update prototype" : "Create prototype"}
        </h5>
    </svelte:fragment>

    <form id={formId} class="content" on:submit|preventDefault={submit}>
        <Field class="form-field required" name="title" let:uniqueId>
            <label for={uniqueId}>Title</label>
            <input
                type="text"
                id={uniqueId}
                on:input={() => {
                    isTitleDirty = true;
                }}
                required
                bind:value={prototype.title}
            />
        </Field>

        <div class="block m-b-10">Type<sup class="txt-danger">*</sup></div>
        <PrototypeTypeField bind:prototype />
    </form>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent" disabled={isSubmitting} on:click={() => hide()}>
            <span class="txt">Cancel</span>
        </button>
        <button
            type="submit"
            form={formId}
            class="btn btn-expanded btn-primary m-l-auto"
            disabled={isSubmitting}
            class:btn-loading={isSubmitting}
        >
            <span class="txt">
                {prototype.id ? "Update prototype" : "Create prototype"}
            </span>
        </button>
    </svelte:fragment>
</OverlayPanel>
