<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import tooltip from "@/actions/tooltip";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";
    import MultipleValueInput from "@/components/base/MultipleValueInput.svelte";

    const dispatch = createEventDispatcher();
    const formId = "share_" + utils.randomString(5);

    export let active = false;

    let panel;
    let link = {};
    let emails = [];
    let message = "";
    let isSubmitting = false;

    $: url = utils.getProjectLinkURL(link);

    export function show(upsert = null) {
        reset();

        link = upsert ? structuredClone(upsert) : {};

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    function reset() {
        setErrors({});
        isSubmitting = false;
        emails = [];
        message = "";
    }

    async function submit() {
        isSubmitting = true;

        try {
            await pb.send("/api/pr/share/" + encodeURIComponent(link.id), {
                method: "POST",
                body: { emails, message },
            });

            isSubmitting = false;

            hide();

            dispatch("submit");

            addSuccessToast("Successfully shared the project link!");
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
    bind:active
    popup
    on:show
    on:hide
    beforeHide={() => !isSubmitting}
    {...$$restProps}
>
    <svelte:fragment slot="header">
        <div class="flex-fill">
            <button
                type="button"
                class="btn btn-circle btn-sm btn-transparent btn-hint"
                use:tooltip={{ position: "right", text: "Back" }}
                on:click={hide}
            >
                <i class="iconoir-arrow-left" />
            </button>
        </div>
        <h5 class="title">Share project link</h5>
        <div class="flex-fill">&nbsp;</div>
    </svelte:fragment>

    <form id={formId} class="grid grid-sm" on:submit|preventDefault={submit}>
        {#if link.id}
            <div class="col-12">
                <div class="alert alert-warning m-0">
                    <div class="content txt-center txt-bold">
                        <a
                            href={url}
                            target="_blank"
                            rel="noopener noreferrer"
                            use:tooltip={"Open in new tab"}
                        >
                            {url}
                        </a>
                    </div>
                </div>
            </div>
        {/if}

        <Field class="form-field required" name="emails" let:uniqueId>
            <label for={uniqueId}>Email(s)</label>
            <MultipleValueInput id={uniqueId} bind:value={emails} required autofocus />
            <div class="help-block">Separate multiple emails with comma.</div>
        </Field>

        <Field class="form-field" name="message" let:uniqueId>
            <label for={uniqueId}>Additional message</label>
            <textarea
                id={uniqueId}
                bind:value={message}
                rows="3"
                placeholder={link.passwordProtected ? "eg. The password is..." : ""}
            />
        </Field>
    </form>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent m-r-auto" disable={isSubmitting} on:click={hide}>
            <span class="txt">Back</span>
        </button>

        <button
            type="submit"
            form={formId}
            class="btn btn-expanded-sm btn-primary"
            class:btn-loading={isSubmitting}
            disabled={isSubmitting}
        >
            <i class="iconoir-send-mail" />
            <span class="txt">Send</span>
        </button>
    </svelte:fragment>
</OverlayPanel>
