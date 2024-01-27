<script>
    import { link, replace } from "svelte-spa-router";
    import { getTokenPayload } from "pocketbase";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import Field from "@/components/base/Field.svelte";
    import { addSuccessToast } from "@/stores/toasts";

    export let params;

    const pageTitle = "Email change";

    let password = "";
    let isSubmitting = false;

    $: newEmail = getTokenPayload(params?.token).newEmail || "";

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").confirmEmailChange(params?.token, password);

            // reauthenticate
            await pb.collection("users").authWithPassword(newEmail, password);

            addSuccessToast("Successfully updated your email address.");

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
            <h4 class="m-b-sm">{pageTitle}</h4>
            <p class="m-b-sm">
                Enter your password to confirm your new email:
                {#if newEmail}
                    <br />
                    <strong>{newEmail}</strong>
                {/if}
            </p>
            <Field class="form-field form-field-lg" name="password" let:uniqueId>
                <div class="field-group">
                    <label
                        for={uniqueId}
                        class="addon prefix"
                        use:tooltip={{ position: "left", text: "Password" }}
                    >
                        <i class="iconoir-lock" />
                    </label>
                    <input
                        required
                        type="password"
                        id={uniqueId}
                        placeholder="Password"
                        bind:value={password}
                    />
                </div>
            </Field>

            <button
                type="submit"
                class="btn btn-block btn-lg btn-primary"
                class:btn-disabled={isSubmitting}
                class:btn-loading={isSubmitting}
            >
                <span class="txt">Confirm email change</span>
            </button>
        </form>

        <div class="block txt-center m-t-base">
            <a href="/login" use:link class="fade link-prev">
                <div class="txt">Back to Login</div>
            </a>
        </div>
    </div>
</Layout>
