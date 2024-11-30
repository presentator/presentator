<script>
    import { link, replace } from "svelte-spa-router";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import Field from "@/components/base/Field.svelte";
    import { addSuccessToast } from "@/stores/toasts";

    export let params;

    const pageTitle = "Reset password";

    let password = "";
    let passwordConfirm = "";
    let isSubmitting = false;

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").confirmPasswordReset(params?.token, password, passwordConfirm);

            pb.authStore.clear();

            addSuccessToast("You can now login with your new password.", 4000);

            replace("/login");
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

            <Field class="form-field form-field-lg" name="password" let:uniqueId>
                <div class="field-group">
                    <label
                        for={uniqueId}
                        class="addon prefix"
                        use:tooltip={{ position: "left", text: "New password" }}
                    >
                        <i class="iconoir-lock" />
                    </label>
                    <input
                        required
                        type="password"
                        id={uniqueId}
                        placeholder="New password"
                        bind:value={password}
                    />
                </div>
            </Field>

            <Field class="form-field form-field-lg" name="passwordConfirm" let:uniqueId>
                <div class="field-group">
                    <label
                        for={uniqueId}
                        class="addon prefix"
                        use:tooltip={{ position: "left", text: "New password confirm" }}
                    >
                        <i class="iconoir-lock" />
                    </label>
                    <input
                        required
                        type="password"
                        id={uniqueId}
                        placeholder="New password confirm"
                        bind:value={passwordConfirm}
                    />
                </div>
            </Field>

            <button
                type="submit"
                class="btn btn-block btn-lg btn-primary"
                class:btn-disabled={isSubmitting}
                class:btn-loading={isSubmitting}
            >
                <span class="txt">Reset password</span>
            </button>
        </form>

        <div class="block txt-center m-t-base">
            <a href="/login" use:link class="fade link-prev">
                <div class="txt">Back to Login</div>
            </a>
        </div>
    </div>
</Layout>
