<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";

    const dispatch = createEventDispatcher();

    export let mfaId = null;
    export let providers = [];

    let missingImages = {};

    function oauth2(providerName) {
        pb.collection("users")
            .authWithOAuth2({
                provider: providerName,
                mfaId: mfaId,
                createData: {
                    allowEmailNotifications: true,
                },
            })
            .then(() => {
                dispatch("submit", {});
            })
            .catch((err) => {
                if (err.isAbort) {
                    return;
                }

                if (err.response?.mfaId) {
                    mfaId = err.response.mfaId;
                    addWarningToast("Second authentication factor is required.");
                    dispatch("submit", { mfaId });
                } else {
                    pb.error(err);
                }
            });
    }
</script>

<nav class="auth-providers-list">
    {#each providers as provider}
        <button
            type="button"
            class="auth-provider"
            use:tooltip={provider.displayName}
            on:click={() => oauth2(provider.name)}
        >
            {#if missingImages[provider.name]}
                <i class="iconoir-fingerprint" />
            {:else}
                <img
                    src="{pb.baseURL}/_/images/oauth2/{provider.name}.svg"
                    alt="{provider.displayName} logo"
                    on:error={() => {
                        missingImages[provider.name] = true;
                    }}
                    on:load={() => {
                        delete missingImages[provider.name];
                    }}
                />
            {/if}
        </button>
    {/each}
</nav>

<style lang="scss">
    .auth-providers-list {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
        align-items: center;
        justify-content: center;
        gap: var(--smSpacing);
    }
    .auth-provider {
        --providerSize: 55px;

        display: inline-flex;
        flex-shrink: 0;
        cursor: pointer;
        align-items: center;
        justify-content: center;
        width: var(--providerSize);
        height: var(--providerSize);
        color: var(--txtBaseColor);
        text-decoration: none;
        outline: 0;
        font-size: 1.4rem;
        background: #fff;
        border-radius: 100px;
        border: 2px solid var(--baseAlt3Color);
        transition:
            background var(--baseAnimationSpeed),
            border var(--baseAnimationSpeed);
        img {
            width: auto;
            height: auto;
            max-width: 40%;
            max-height: 40%;
        }
        &:hover,
        &:focus-visible,
        &:active {
            background: var(--baseAlt1Color);
        }
        &:active {
            background: var(--baseAlt2Color);
        }
    }
</style>
