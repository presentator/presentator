<script>
    import { replace } from "svelte-spa-router";
    import pb from "@/pb";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import { addSuccessToast, removeAllToasts } from "@/stores/toasts";

    export let params;

    const pageTitle = "Confirm verification";

    let success = true;
    let isSubmitting = false;

    submit();

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").confirmVerification(params?.token);

            success = true;

            removeAllToasts();

            addSuccessToast("Successfully activated your account.", 4000);

            replace("/login");
        } catch (err) {
            success = false;
        }

        isSubmitting = false;
    }
</script>

<Layout fullpage header={false} title={pageTitle}>
    <div class="wrapper wrapper-sm m-b-base entrance-top">
        <header class="block txt-center m-b-base">
            <Logo />
        </header>

        <div class="panel txt-center">
            <h4 class="m-b-base">{pageTitle}</h4>

            {#if isSubmitting}
                <span class="loader" />
            {:else if !success}
                <div class="alert alert-danger m-0">
                    <div class="content txt-center">
                        <p>Invalid or expired verification token.</p>
                    </div>
                </div>
            {/if}
        </div>
    </div>
</Layout>
