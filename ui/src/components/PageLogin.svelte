<script>
    import { link } from "svelte-spa-router";
    import { slide } from "svelte/transition";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import Field from "@/components/base/Field.svelte";
    import SocialAuth from "@/components/base/SocialAuth.svelte";
    import { addErrorToast, removeAllToasts } from "@/stores/toasts";

    const pageTitle = "Login";

    let identityLabel = "";
    let authMethods = {};
    let isLoadingAuthMethods = false;
    let identity = "";
    let password = "";
    let isSubmitting = false;

    $: hasPasswordAuth = authMethods.emailPassword || authMethods.usernamePassword;

    $: if (authMethods.emailPassword && authMethods.usernamePassword) {
        identityLabel = "Username or email";
    } else if (authMethods.emailPassword) {
        identityLabel = "Email";
    } else if (authMethods.usernamePassword) {
        identityLabel = "Username";
    } else {
        identityLabel = "";
    }

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

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").authWithPassword(identity, password);

            removeAllToasts();

            pb.replaceWithRemembered();
        } catch (err) {
            addErrorToast("Invalid login credentials or unverified user.");
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

            {#if isLoadingAuthMethods}
                <span class="loader" />
            {:else}
                <div class="panel-content" transition:slide={{ duration: 300 }}>
                    {#if hasPasswordAuth}
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
                            <a href="/forgotten-password" class="link-primary txt-sm" use:link>
                                Forgotten password?
                            </a>
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
                    {/if}

                    {#if authMethods.authProviders.length}
                        {#if hasPasswordAuth}
                            <p class="m-t-base m-b-sm">Or sign in with</p>
                        {/if}

                        <SocialAuth providers={authMethods.authProviders} />
                    {/if}
                </div>
            {/if}
        </form>

        <div class="block txt-center m-t-base">
            <a href="/register" class="fade" use:link>
                Don't have an account yet? <strong>Register!</strong>
            </a>
        </div>
    </div>
</Layout>
