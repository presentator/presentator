<script>
    import { createEventDispatcher } from "svelte";
    import { link } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import Field from "@/components/base/Field.svelte";
    import { addErrorToast, addWarningToast, removeAllToasts } from "@/stores/toasts";

    const dispatch = createEventDispatcher();

    export let identityFields = [];
    export let mfaId = null;
    export let identity = "";
    export let password = "";

    let identityLabel = "";
    let isSubmitting = false;

    $: identityLabel = utils.sentenize(identityFields?.join(" or ") || "", false);

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").authWithPassword(identity, password, { mfaId });

            removeAllToasts();

            dispatch("submit", {});
        } catch (err) {
            if (err?.response?.mfaId) {
                mfaId = err.response.mfaId;
                addWarningToast("Second authentication factor is required.");
                dispatch("submit", { mfaId });
            } else {
                addErrorToast("Invalid login credentials or unverified user.");
            }
        }

        isSubmitting = false;
    }
</script>

<form on:submit|preventDefault={submit}>
    <Field class="form-field form-field-lg" name="identity" let:uniqueId>
        <div class="field-group">
            <label
                for={uniqueId}
                class="addon prefix"
                use:tooltip={{ position: "left", text: identityLabel }}
            >
                <i class="iconoir-user" />
            </label>
            <!-- svelte-ignore a11y-autofocus -->
            <input
                autofocus
                required
                type="text"
                id={uniqueId}
                placeholder={identityLabel}
                bind:value={identity}
            />
        </div>
    </Field>

    <Field class="form-field form-field-lg" name="password" let:uniqueId>
        <div class="field-group m-b-10">
            <label for={uniqueId} class="addon prefix" use:tooltip={{ position: "left", text: "Password" }}>
                <i class="iconoir-lock" />
            </label>
            <input required type="password" id={uniqueId} placeholder="Password" bind:value={password} />
        </div>
        <a href="/forgotten-password" class="link-primary txt-sm" use:link> Forgotten password? </a>
    </Field>

    <button
        type="submit"
        class="btn btn-block btn-lg btn-primary btn-next"
        class:btn-disabled={isSubmitting}
        class:btn-loading={isSubmitting}
    >
        <span class="txt">Login</span>
        <i class="iconoir-arrow-right" />
    </button>
</form>
