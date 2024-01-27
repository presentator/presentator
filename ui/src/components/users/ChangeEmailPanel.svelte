<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";

    const dispatch = createEventDispatcher();
    const formId = "email_" + utils.randomString(5);

    let panel;
    let newEmail = "";
    let isSubmitting = false;

    export function show() {
        reset();

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    function reset() {
        setErrors({});

        newEmail = "";
    }

    async function submit() {
        isSubmitting = true;

        try {
            await pb.collection("users").requestEmailChange(newEmail);

            isSubmitting = false;

            dispatch("submit");

            addSuccessToast(`A confirmation link was sent to ${newEmail}.`);

            hide();
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
    beforeHide={() => !isSubmitting}
    popup
    class="overlay-panel-sm"
    on:show
    on:hide
>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">Change email</h5>
    </svelte:fragment>

    <form id={formId} class="grid grid-sm" on:submit|preventDefault={submit}>
        <Field class="form-field required" name="newEmail" let:uniqueId>
            <label for={uniqueId}>New email</label>
            <!-- svelte-ignore a11y-autofocus -->
            <input type="email" id={uniqueId} bind:value={newEmail} autofocus required />
        </Field>
    </form>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent m-r-auto" disabled={isSubmitting} on:click={hide}>
            <span class="txt">Cancel</span>
        </button>

        <button
            form={formId}
            type="submit"
            class="btn btn-expanded-sm btn-primary"
            class:btn-loading={isSubmitting}
            disabled={isSubmitting}
        >
            <span class="txt">Send confirmation link</span>
        </button>
    </svelte:fragment>
</OverlayPanel>
