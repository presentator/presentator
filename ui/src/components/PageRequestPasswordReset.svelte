<script>
    import { link } from "svelte-spa-router";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import Field from "@/components/base/Field.svelte";
    import { removeAllToasts } from "@/stores/toasts";

    const pageTitle = "Forgotten password";

    let email = "";
    let isSubmitting = false;
    let showSuccessAlert = false;

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").requestPasswordReset(email);

            removeAllToasts();

            showSuccessAlert = true;
        } catch (err) {
            pb.error(err);
        }

        isSubmitting = false;
    }
</script>

<Layout fullpage header={false} title={pageTitle}>
    <div class="wrapper wrapper-sm m-b-base entrance-top">
        <header class="block txt-center m-b-base">
            <Logo />
        </header>

        <form class="panel txt-center" on:submit|preventDefault={submit}>
            <h4 class="m-b-base">{pageTitle}</h4>

            {#if showSuccessAlert}
                <div class="alert m-0">
                    <div class="content txt-center">
                        <p>
                            Go check your email for the recovery link:
                            <br />
                            <strong>{email}</strong>
                        </p>
                    </div>
                </div>
            {:else}
                <p class="m-b-sm">
                    Enter the email associated with your account and we'll send you a recovery link:
                </p>

                <Field class="form-field form-field-lg" name="email" let:uniqueId>
                    <div class="field-group">
                        <label
                            for={uniqueId}
                            class="addon prefix"
                            use:tooltip={{ position: "left", text: "Email" }}
                        >
                            <i class="iconoir-mail" />
                        </label>
                        <!-- svelte-ignore a11y-autofocus -->
                        <input
                            autofocus
                            required
                            type="email"
                            id={uniqueId}
                            placeholder="Email"
                            bind:value={email}
                        />
                    </div>
                </Field>

                <button
                    type="submit"
                    class="btn btn-block btn-lg btn-primary"
                    class:btn-disabled={isSubmitting}
                    class:btn-loading={isSubmitting}
                >
                    <i class="iconoir-send-mail" />
                    <span class="txt">Send recovery link</span>
                </button>
            {/if}
        </form>

        <div class="block txt-center m-t-base">
            <a href="/login" use:link class="fade link-prev">
                <div class="txt">Back to Login</div>
            </a>
        </div>
    </div>
</Layout>
