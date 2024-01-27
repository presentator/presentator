<script>
    import pb from "@/pb";
    import utils from "@/utils";
    import { loggedUser, options } from "@/stores/app";
    import Logo from "@/components/base/Logo.svelte";
    import Toggler from "@/components/base/Toggler.svelte";
    import UserThumb from "@/components/users/UserThumb.svelte";
    import NotificationsBtn from "@/components/notifications/NotificationsBtn.svelte";
    import UserSettingsPanel from "@/components/users/UserSettingsPanel.svelte";

    let classes = "";
    export { classes as class }; // export reserved keyword

    export let title = "";
    export let header = true;
    export let footer = true;
    export let fullpage = false;

    let userPanel;
</script>

<svelte:head>
    <title>{utils.joinNonEmpty([title, "Presentator"], " - ")}</title>
</svelte:head>

<div class="app-layout {classes}" class:fullpage>
    {#if header}
        <header class="app-header">
            <Logo class="app-logo-sm" withText={false} />

            <slot name="header-left" />

            <div class="inline-flex flex-gap-sm m-l-auto">
                <slot name="header-right" />

                <NotificationsBtn />

                <UserThumb tabindex="0" class="thumb thumb-dark thumb-circle thumb-handle" user={$loggedUser}>
                    <Toggler class="dropdown">
                        <button
                            type="button"
                            class="dropdown-item closable"
                            on:click|stopPropagation={() => userPanel?.show($loggedUser)}
                        >
                            <i class="iconoir-settings" />
                            <span class="txt">Settings</span>
                        </button>
                        <hr />
                        <button type="button" class="dropdown-item" on:click={() => pb.logout()}>
                            <i class="iconoir-log-out" />
                            <span class="txt">Logout</span>
                        </button>
                    </Toggler>
                </UserThumb>
            </div>
        </header>
    {/if}

    <main class="app-body">
        <div class="app-content">
            <slot />
        </div>

        {#if footer}
            <footer class="app-footer">
                {#if $options?.links}
                    {#each Object.entries($options?.links) as [name, url]}
                        <a href={url} target="_blank" rel="noopener noreferrer">
                            {name}
                        </a>
                        <span class="delimiter">|</span>
                    {/each}
                {/if}

                <a href={import.meta.env.PR_RELEASES_URL} target="_blank" rel="noopener noreferrer">
                    <i class="iconoir-github" />
                    <span class="txt">Presentator {import.meta.env.PR_VERSION}</span>
                </a>
            </footer>
        {/if}
    </main>
</div>

<UserSettingsPanel bind:this={userPanel} />
