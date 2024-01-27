<script>
    import { createEventDispatcher, onDestroy } from "svelte";
    import { replace } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import { confirm } from "@/stores/confirmation";
    import { loggedUser } from "@/stores/app";
    import { addSuccessToast } from "@/stores/toasts";
    import { activeProject, addProject } from "@/stores/projects";
    import tooltip from "@/actions/tooltip";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import SearchBar from "@/components/base/SearchBar.svelte";
    import UserListItem from "@/components/users/UserListItem.svelte";

    const dispatch = createEventDispatcher();
    const listRequestKey = "project_owners_list";
    const suggestionsRequestKey = "project_owners_suggestions";

    let panel;
    let search = "";
    let isAdding = false;
    let isRemoving = false;
    let suggestions = [];
    let isLoadingSuggestions = false;
    let currentAdmins = [];
    let isLoadingCurrentAdmins = false;
    let searchDebounceId;

    $: if (search) {
        isLoadingSuggestions = true;
        clearTimeout(searchDebounceId);
        searchDebounceId = setTimeout(loadSuggestions, 100);
    } else {
        clearTimeout(searchDebounceId);
        pb.cancelRequest(suggestionsRequestKey);
        suggestions = [];
    }

    $: if ($activeProject?.users !== -1) {
        loadCurrentAdmins();
    }

    export function show() {
        loadCurrentAdmins();

        return panel?.show();
    }

    export function hide() {
        pb.cancelRequest(listRequestKey);
        pb.cancelRequest(suggestionsRequestKey);
        clearTimeout(searchDebounceId);
        return panel?.hide();
    }

    async function loadCurrentAdmins() {
        if (!$activeProject?.users?.length) {
            return;
        }

        currentAdmins = [];

        isLoadingCurrentAdmins = true;

        try {
            const items = await pb.collection("users").getFullList({
                filter: $activeProject.users.map((id) => `id="${id}"`).join("||"),
                requestKey: listRequestKey,
            });

            // preserve selection order
            for (let id of $activeProject.users) {
                const item = items.find((item) => item.id == id);
                if (item) {
                    currentAdmins.push(item);
                }
            }

            isLoadingCurrentAdmins = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isLoadingCurrentAdmins = false;
            }
        }
    }

    async function loadSuggestions() {
        // don't clear the suggestions so that we can show the
        // same number of skeleton loaders
        // suggestions = [];

        isLoadingSuggestions = true;

        try {
            let filter = ["username", "name", "email"]
                .map((f) => `${f}~"${search.replaceAll('"', "")}"`)
                .join("||");

            if ($activeProject?.users?.length) {
                filter = $activeProject?.users?.map((id) => `id!="${id}"`).join("&&") + ` && (${filter})`;
            }

            const result = await pb.collection("users").getList(1, 30, {
                filter: filter,
                requestKey: suggestionsRequestKey,
            });

            suggestions = result.items;

            isLoadingSuggestions = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isLoadingSuggestions = false;
            }
        }
    }

    function addAdminWithConfirm(user) {
        confirm(
            `Do you really want to add user ${utils.getUserDisplayName(user)} to the project owners?`,
            async () => addAdmin(user)
        );
    }

    async function addAdmin(user) {
        if (isAdding || !user?.id) {
            return;
        }

        isAdding = true;

        try {
            const project = await pb.collection("projects").update($activeProject.id, {
                "users+": user.id,
            });

            addProject(project);

            utils.removeByKey(suggestions, "id", user.id);
            suggestions = suggestions;

            // reset search if there are no more suggestions
            if (!suggestions.length) {
                search = "";
            }

            addSuccessToast(
                `Successfully added ${utils.getUserDisplayName(user)} to the project owners list.`
            );

            dispatch("add", user);

            isAdding = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isAdding = false;
            }
        }
    }

    function removeAdminWithConfirm(user) {
        let message = `Do you really want to remove the project access of user ${utils.getUserDisplayName(
            user
        )}?`;

        if ($loggedUser.id == user.id) {
            message = `You no longer will have access to the project! \n\nDo you really want to remove your user from the project owners list?`;
        }

        confirm(message, async () => removeAdmin(user));
    }

    async function removeAdmin(user) {
        if (isRemoving || !user?.id) {
            return;
        }

        isRemoving = true;

        try {
            const project = await pb.collection("projects").update($activeProject.id, {
                "users-": user.id,
            });

            addProject(project);

            if ($loggedUser.id == user.id) {
                addSuccessToast(
                    `Successfully removed your user from the ${$activeProject.title} owners list.`
                );

                replace("/projects");
            } else {
                addSuccessToast(
                    `Successfully removed ${utils.getUserDisplayName(user)} from the ${
                        $activeProject.title
                    } owners list.`
                );
            }

            dispatch("remove", user);

            utils.removeByKey(currentAdmins, "id", user.id);
            currentAdmins = currentAdmins;

            // reload the suggestions
            if (search) {
                loadSuggestions();
            }

            isRemoving = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isRemoving = false;
            }
        }
    }

    onDestroy(() => {
        hide();
    });
</script>

<OverlayPanel
    bind:this={panel}
    popup
    overlayClose={!isAdding}
    escHide={!isAdding}
    beforeHide={() => !isAdding}
    on:show
    on:hide>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">Manage project owners</h5>
    </svelte:fragment>

    <h6 class="m-b-sm">Current owners</h6>
    <div class="list list-current-owners">
        {#if isLoadingCurrentAdmins}
            {#each Array($activeProject.users.length || 1) as _}
                <div class="list-item">
                    <figure class="thumb thumb-circle" />
                    <div class="content">
                        <div class="skeleton-loader" />
                    </div>
                </div>
            {/each}
        {:else}
            {#each currentAdmins as user (user.id)}
                <UserListItem {user}>
                    <button
                        type="button"
                        class="btn btn-sm btn-semitransparent btn-danger"
                        disabled={$activeProject.users.length == 1 || isRemoving}
                        use:tooltip={$activeProject.users.length == 1
                            ? "You can't remove the only project owner"
                            : ""}
                        on:click|preventDefault={() => removeAdminWithConfirm(user)}>
                        <span class="txt">Revoke access</span>
                    </button>
                </UserListItem>
            {:else}
                <div class="list-item txt-hint">
                    <div class="block txt-center p-5">
                        <span class="txt-hint">The project doesn't have any owners yet.</span>
                    </div>
                </div>
            {/each}
        {/if}
    </div>

    <hr />

    <SearchBar bind:value={search} class="m-b-0" placeholder="Search users..." />

    {#if isLoadingSuggestions || search}
        <div class="clearfix m-b-sm" />
    {/if}

    <div class="list list-suggestions">
        {#if isLoadingSuggestions}
            {#each Array(suggestions.length || 1) as _}
                <div class="list-item">
                    <figure class="thumb thumb-circle" />
                    <div class="content">
                        <div class="skeleton-loader" />
                    </div>
                </div>
            {/each}
        {:else if suggestions.length}
            {#each suggestions as user (user.id)}
                <UserListItem {user}>
                    <button
                        type="button"
                        class="btn btn-sm btn-expanded-sm btn-semitransparent"
                        disabled={isAdding}
                        on:click|preventDefault={() => addAdminWithConfirm(user)}>
                        <span class="txt">Add</span>
                    </button>
                </UserListItem>
            {/each}
        {:else if search}
            <div class="list-item">
                <div class="block txt-center">
                    <span class="txt-hint">No users found.</span>
                </div>
            </div>
        {/if}
    </div>

    <svelte:fragment slot="footer">
        <button
            type="button"
            class="btn btn-transparent m-r-auto"
            disabled={isAdding || isRemoving}
            on:click={() => hide()}>
            <span class="txt">Close</span>
        </button>
    </svelte:fragment>
</OverlayPanel>

<style>
    .list-suggestions .list-item {
        min-height: 38px;
    }
</style>
