<script>
    import { createEventDispatcher, onDestroy } from "svelte";
    import { replace } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";

    const dispatch = createEventDispatcher();
    const formId = "report_" + utils.randomString(5);

    export let active = false;

    let panel;
    let message = "";
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

        message = "";
    }

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.send("/api/pr/report", {
                method: "POST",
                body: { message },
            });

            dispatch("submit");

            addSuccessToast(
                "Thank you, we will investigate and remove the project if found inappropriate.",
                5000,
            );

            replace("/");
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }

        isSubmitting = false;
    }

    onDestroy(() => {
        hide();
    });
</script>

<OverlayPanel bind:this={panel} bind:active popup on:show on:hide>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">Report this design for spam or abusive content</h5>
    </svelte:fragment>

    <form id={formId} on:submit|preventDefault={submit}>
        <Field class="form-field m-0" name="message" let:uniqueId>
            <label for={uniqueId}>Additional details</label>
            <textarea id={uniqueId} rows="3" bind:value={message} />
        </Field>
    </form>

    <svelte:fragment slot="footer">
        <button
            type="button"
            class="btn btn-transparent m-r-auto"
            disabled={isSubmitting}
            on:click={() => hide()}
        >
            <span class="txt">Close</span>
        </button>

        <button
            form={formId}
            type="submit"
            class="btn btn-expanded-sm btn-primary"
            class:btn-loading={isSubmitting}
            disabled={isSubmitting}
        >
            <span class="txt">Report</span>
        </button>
    </svelte:fragment>
</OverlayPanel>
