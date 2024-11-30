<script>
    import { link } from "svelte-spa-router";
    import pb from "@/pb";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import SocialAuth from "@/components/base/SocialAuth.svelte";
    import OTPForm from "@/components/base/OTPForm.svelte";
    import PasswordAuthForm from "@/components/base/PasswordAuthForm.svelte";
    import { removeAllToasts } from "@/stores/toasts";

    const pageTitle = "Login";

    let authMethods = {};
    let isLoadingAuthMethods = false;
    let mfaId = null;
    let identity = "";
    let usedAuthMethods = [];

    $: hasOAuth2 = authMethods.oauth2?.enabled && authMethods.oauth2?.providers?.length;

    $: hasOTP = authMethods.otp?.enabled;

    $: hasPasswordAuth = authMethods.password?.enabled;

    $: canShowPasswordAuth = hasPasswordAuth && !usedAuthMethods.includes("passwordAuth");

    // note: currently show the otp only if password auth is disabled or as second auth factor
    $: canShowOTP = !canShowPasswordAuth && hasOTP && !usedAuthMethods.includes("otp");

    loadAuthMethods();

    async function loadAuthMethods() {
        isLoadingAuthMethods = true;

        try {
            authMethods = await pb.collection("users").listAuthMethods();

            isLoadingAuthMethods = false;
        } catch (err) {
            if (err?.isAbort) {
                pb.error(err);
                isLoadingAuthMethods = false;
            }
        }
    }

    function afterAuthSubmit(authMethod, receivedMFAId) {
        usedAuthMethods.push(authMethod);
        usedAuthMethods = usedAuthMethods;

        if (!receivedMFAId) {
            removeAllToasts();
            pb.replaceWithRemembered();
        }
    }
</script>

<Layout fullpage header={false} title={pageTitle}>
    <div class="wrapper wrapper-sm m-b-base entrance-top">
        <header class="block txt-center m-b-base">
            <Logo />
        </header>

        <div class="panel txt-center">
            <h4 class="m-b-base">{pageTitle}</h4>

            {#if isLoadingAuthMethods}
                <span class="loader" />
            {:else}
                <div class="panel-content">
                    {#if canShowPasswordAuth}
                        <PasswordAuthForm
                            bind:mfaId
                            bind:identity
                            identityFields={authMethods.password.identityFields}
                            on:submit={(e) => afterAuthSubmit("passwordAuth", e.detail?.mfaId)}
                        />
                    {/if}

                    {#if canShowOTP}
                        <OTPForm
                            bind:mfaId
                            email={identity.includes("@") ? identity : ""}
                            on:submit={(e) => afterAuthSubmit("otp", e.detail?.mfaId)}
                        />
                    {/if}

                    {#if hasOAuth2 && !usedAuthMethods.includes("oaut2")}
                        {#if canShowPasswordAuth || canShowOTP}
                            <p class="m-t-base m-b-sm">Or sign in with</p>
                        {:else}
                            <p class="m-b-sm">Sign in with</p>
                        {/if}

                        <SocialAuth
                            providers={authMethods.oauth2.providers}
                            bind:mfaId
                            on:submit={(e) => afterAuthSubmit("oauth2", e.detail?.mfaId)}
                        />
                    {/if}
                </div>
            {/if}
        </div>

        <div class="block txt-center m-t-base">
            <a href="/register" class="fade" use:link>
                Don't have an account yet? <strong>Register!</strong>
            </a>
        </div>
    </div>
</Layout>
