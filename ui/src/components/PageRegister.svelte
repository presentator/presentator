<script>
    import { link, replace } from "svelte-spa-router";
    import pb from "@/pb";
    import { options } from "@/stores/app";
    import tooltip from "@/actions/tooltip";
    import Layout from "@/components/base/Layout.svelte";
    import Logo from "@/components/base/Logo.svelte";
    import Field from "@/components/base/Field.svelte";
    import SocialAuth from "@/components/base/SocialAuth.svelte";
    import { removeAllToasts } from "@/stores/toasts";

    const pageTitle = "Register";

    let email = "";
    let username = "";
    let password = "";
    let passwordConfirm = "";
    let isSubmitting = false;
    let authMethods = {};
    let isLoadingAuthMethods = false;
    let showSuccessAlert = false;
    let isUsernameDirty = false;

    $: hasMFA = authMethods.mfa?.enabled;

    $: hasPasswordAuth = authMethods.password?.enabled;

    $: hasOAuth2 = authMethods.oauth2?.enabled && authMethods.oauth2?.providers?.length;

    $: emailLocalPart = email.split("@")?.[0] || "";

    $: if (!isUsernameDirty) {
        username = emailLocalPart;
    }

    loadAuthMethods();

    async function loadAuthMethods() {
        isLoadingAuthMethods = true;

        try {
            authMethods = await pb.collection("users").listAuthMethods();
        } catch (err) {
            pb.error(err);
        }

        isLoadingAuthMethods = false;
    }

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            await pb.collection("users").create({
                email,
                username,
                password,
                passwordConfirm,
                allowEmailNotifications: true,
            });

            removeAllToasts();

            if (email) {
                await pb.collection("users").requestVerification(email);
            }

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

        <form class="panel txt-center" on:submit|preventDefault={submit} autocomplete="off">
            <h4 class="m-b-base">{pageTitle}</h4>

            {#if showSuccessAlert}
                <div class="alert m-0">
                    <div class="content txt-center">
                        <p>
                            Go check your email to verify your account:
                            <br />
                            <strong>{email}</strong>
                        </p>
                    </div>
                </div>
            {:else if isLoadingAuthMethods}
                <span class="loader" />
            {:else}
                <div class="panel-content">
                    {#if hasPasswordAuth}
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
                                    type="email"
                                    id={uniqueId}
                                    name="register_email"
                                    placeholder="Email"
                                    bind:value={email}
                                />
                            </div>
                        </Field>

                        <Field class="form-field form-field-lg" name="username" let:uniqueId>
                            <div class="field-group">
                                <label
                                    for={uniqueId}
                                    class="addon prefix"
                                    use:tooltip={{ position: "left", text: "Username" }}
                                >
                                    <i class="iconoir-user" />
                                </label>
                                <input
                                    type="text"
                                    id={uniqueId}
                                    name="register_username"
                                    placeholder="Username"
                                    on:input={() => {
                                        isUsernameDirty = true;
                                    }}
                                    bind:value={username}
                                />
                            </div>
                        </Field>

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
                                    name="register_password"
                                    placeholder="Password"
                                    bind:value={password}
                                />
                            </div>
                        </Field>

                        <Field class="form-field form-field-lg" name="passwordConfirm" let:uniqueId>
                            <div class="field-group">
                                <label
                                    for={uniqueId}
                                    class="addon prefix"
                                    use:tooltip={{ position: "left", text: "Password confirm" }}
                                >
                                    <i class="iconoir-lock" />
                                </label>
                                <input
                                    required
                                    type="password"
                                    id={uniqueId}
                                    name="register_passwordConfirm"
                                    placeholder="Password confirm"
                                    autocomplete="new-password"
                                    bind:value={passwordConfirm}
                                />
                            </div>
                        </Field>

                        <button
                            type="submit"
                            class="btn btn-block btn-lg btn-primary btn-next"
                            class:btn-disabled={isSubmitting}
                            class:btn-loading={isSubmitting}
                        >
                            <span class="txt">Register</span>
                            <i class="iconoir-arrow-right" />
                        </button>
                        <p class="m-t-xs m-b-sm txt-sm txt-hint">
                            By registering you agree with our
                            <a href={$options.termsURL} target="_blank" rel="noreferrer noopener">
                                Terms and Privacy policy
                            </a>
                        </p>
                    {/if}

                    {#if hasOAuth2}
                        {#if hasPasswordAuth}
                            <p class="m-t-base m-b-sm">Or sign up with</p>
                        {:else}
                            <p class="m-b-sm">Sign up with</p>
                        {/if}

                        <SocialAuth
                            providers={authMethods.oauth2.providers}
                            on:submit={(e) => {
                                if (!e.detail?.mfaId) {
                                    removeAllToasts();
                                    pb.replaceWithRemembered();
                                } else {
                                    replace("/login");
                                }
                            }}
                        />
                    {/if}
                </div>
            {/if}
        </form>

        <div class="block txt-center m-t-base">
            <a href="/login" class="fade" use:link>
                Already have an account? <strong>Login!</strong>
            </a>
        </div>
    </div>
</Layout>
