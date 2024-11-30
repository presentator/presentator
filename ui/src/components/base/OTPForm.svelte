<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import Field from "@/components/base/Field.svelte";
    import { addWarningToast, removeAllToasts } from "@/stores/toasts";

    const dispatch = createEventDispatcher();

    export let mfaId = null;
    export let email = "";

    let otpId = "";
    let password = "";
    let isSubmitting = false;

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        if (otpId) {
            await confirmOTP();
        } else {
            await requestOTP();
        }

        isSubmitting = false;
    }

    async function requestOTP() {
        otpId = "";

        try {
            const result = await pb.collection("users").requestOTP(email);

            otpId = result.otpId;

            removeAllToasts();
        } catch (err) {
            pb.error(err);
        }
    }

    async function confirmOTP() {
        try {
            await pb.collection("users").authWithOTP(otpId, password, { mfaId });

            dispatch("submit", {});
        } catch (err) {
            if (err?.response?.mfaId) {
                mfaId = err.response.mfaId;
                addWarningToast("Second authentication factor is required.");
                dispatch("submit", { mfaId });
            } else {
                pb.error(err);
            }
        }
    }
</script>

<form on:submit|preventDefault={submit}>
    {#if otpId}
        <p class="m-b-sm">Check your email and enter below the received OTP:</p>

        <Field class="form-field form-field-lg" name="password" let:uniqueId>
            <div class="field-group">
                <label for={uniqueId} class="addon prefix" use:tooltip={{ position: "left", text: "Email" }}>
                    <i class="iconoir-lock" />
                </label>
                <!-- svelte-ignore a11y-autofocus -->
                <input
                    autofocus
                    required
                    type="password"
                    id={uniqueId}
                    placeholder={"OTP"}
                    bind:value={password}
                />
            </div>
        </Field>
    {:else}
        <Field class="form-field form-field-lg" name="email" let:uniqueId>
            <div class="field-group">
                <label for={uniqueId} class="addon prefix" use:tooltip={{ position: "left", text: "Email" }}>
                    <i class="iconoir-mail-open" />
                </label>
                <!-- svelte-ignore a11y-autofocus -->
                <input
                    autofocus
                    required
                    type="email"
                    id={uniqueId}
                    placeholder={"Email"}
                    bind:value={email}
                />
            </div>
        </Field>
    {/if}

    <button
        type="submit"
        class="btn btn-block btn-lg btn-primary btn-next"
        class:btn-disabled={isSubmitting}
        class:btn-loading={isSubmitting}
    >
        {#if !otpId}
            <i class="iconoir-send-mail" />
            <span class="txt">Send OTP</span>
        {:else}
            <span class="txt">Login</span>
            <i class="iconoir-arrow-right" />
        {/if}
    </button>
</form>
