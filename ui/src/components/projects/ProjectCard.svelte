<script>
    import { push } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import { confirm } from "@/stores/confirmation";
    import { addSuccessToast } from "@/stores/toasts";
    import { notifications } from "@/stores/notifications";
    import { removeProject } from "@/stores/projects";
    import Toggler from "@/components/base/Toggler.svelte";
    import LazyImg from "@/components/base/LazyImg.svelte";
    import InlineTitleEdit from "@/components/base/InlineTitleEdit.svelte";

    export let project;

    let featuredScreens = [];

    $: loadFeaturedScreens(project);

    $: totalUnread = $notifications.filter(
        (n) => !n.read && n?.expand?.comment?.expand?.screen?.expand?.["prototype"]?.project == project.id,
    ).length;

    function loadFeaturedScreens(project) {
        featuredScreens = []; // reset

        // extract the last 3 prototypes
        const lastPrototypes =
            project?.expand?.["prototypes(project)"]
                ?.sort(function (a, b) {
                    if (a.created < b.created) {
                        return -1;
                    }
                    if (a.created > b.created) {
                        return 1;
                    }
                    return 0;
                })
                ?.slice(-3) || [];

        for (let p of lastPrototypes) {
            let screen = null;

            // try to locate the first screen from the ordered list
            if (p.screensOrder?.[0]) {
                screen = p.expand?.["screens(prototype)"]?.find((screen) => screen.id == p.screensOrder?.[0]);
            }

            // fallback to the first screen in the expands list
            if (!screen) {
                screen = p.expand?.["screens(prototype)"]?.[0];
            }

            if (screen) {
                featuredScreens.push(screen);
            }
        }
    }

    function goToProject(project, newTab = false) {
        const url = "/projects/" + project.id + "/prototypes";

        if (newTab) {
            window.open("#" + url, "_blank").focus();
        } else {
            push(url);
        }
    }

    async function deleteProjectWithConfirm(project) {
        confirm(
            `Do you really want to delete project ${project.title} and all its related screens?`,
            async () => {
                return deleteProject(project);
            },
            () => {
                document.activeElement?.blur();
            },
        );
    }

    async function deleteProject(project) {
        try {
            await pb.collection("projects").delete(project.id);

            removeProject(project);

            addSuccessToast(`Successfully deleted project ${project.title}.`);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    async function archiveProjectWithConfirm(project) {
        confirm(
            `Do you really want to archive project ${project.title}?`,
            async () => {
                return archiveProject(project);
            },
            () => {
                document.activeElement?.blur();
            },
        );
    }

    async function archiveProject(project) {
        try {
            await pb.collection("projects").update(project.id, { archived: true });

            // remove the project from the store
            // (assuming that we show only archived vs nonarchived)
            if (!project.archived) {
                removeProject(project);
            }

            addSuccessToast(`Successfully archived project ${project.title}.`);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    async function unarchiveProjectWithConfirm(project) {
        confirm(
            `Do you really want to unarchive project ${project.title}?`,
            async () => {
                return unarchiveProject(project);
            },
            () => {
                document.activeElement?.blur();
            },
        );
    }

    async function unarchiveProject(project) {
        try {
            await pb.collection("projects").update(project.id, { archived: false });

            // remove the project from the store
            // (assuming that we show only archived vs nonarchived)
            if (project.archived) {
                removeProject(project);
            }

            addSuccessToast(`Successfully unarchived project ${project.title}.`);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    async function watchToggle(pref) {
        if (!pref?.id) {
            return pref;
        }

        try {
            // optimistic update
            pref.watch = !pref.watch;

            return await pb.collection("projectUserPreferences").update(pref.id, {
                watch: pref.watch,
            });
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    async function favoriteToggle(pref) {
        if (!pref?.id) {
            return pref;
        }

        try {
            // optimistic update
            pref.favorite = !pref.favorite;

            return await pb.collection("projectUserPreferences").update(pref.id, {
                favorite: pref.favorite,
            });
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }
</script>

<div class="card entrance-top">
    <figure class="card-featured">
        {#if featuredScreens.length}
            <div class="card-images-set">
                {#each featuredScreens as screen (screen.id)}
                    <div class="card-img" style:background-color={screen.background}>
                        <LazyImg
                            src={pb.files.getUrl(screen, screen.file, {
                                thumb: "450x0",
                            })}
                            alt={screen.title}
                        />
                    </div>
                {/each}
            </div>
        {:else}
            <i class="iconoir-media-image" />
        {/if}

        <!-- svelte-ignore a11y-click-events-have-key-events -->
        <!-- svelte-ignore a11y-no-static-element-interactions -->
        <div
            class="card-overlay handle"
            on:auxclick={() => {
                goToProject(project, true);
            }}
            on:click={() => {
                goToProject(project);
            }}
        >
            {#if project.expand?.["projectUserPreferences(project)"]?.[0]}
                <div class="ctrl ctrl-top-left">
                    <button
                        type="button"
                        class="btn btn-sm btn-circle btn-transparent"
                        class:fade={!project.expand["projectUserPreferences(project)"][0].favorite}
                        use:tooltip={project.expand["projectUserPreferences(project)"][0].favorite
                            ? "Remove from favorites"
                            : "Add to favorites"}
                        on:click|stopPropagation={(e) => {
                            favoriteToggle(project.expand["projectUserPreferences(project)"][0]);
                            project = project;
                            e.target?.closest("button")?.blur(); // remove btn focus
                        }}
                    >
                        {#if project.expand["projectUserPreferences(project)"][0].favorite}
                            <i class="iconoir-star-solid txt-warning" />
                        {:else}
                            <i class="iconoir-star" />
                        {/if}
                    </button>
                </div>
            {/if}

            <div class="ctrl ctrl-center">
                <i class="iconoir-media-image-list preview-icon" title="View project" />
            </div>
            <div class="ctrl ctrl-top-right" on:click|stopPropagation>
                <button type="button" class="btn btn-sm btn-circle btn-transparent fade">
                    <i class="iconoir-more-vert" />
                    <Toggler class="dropdown dropdown-nowrap dropdown-sm">
                        <div
                            class="dropdown-item"
                            on:click={() => {
                                watchToggle(project.expand["projectUserPreferences(project)"][0]);
                                project = project;
                            }}
                        >
                            {#if project.expand["projectUserPreferences(project)"][0].watch}
                                <i class="iconoir-eye" />
                                <span class="txt">Unwatch</span>
                            {:else}
                                <i class="iconoir-eye-solid" />
                                <span class="txt">Watch</span>
                            {/if}
                        </div>

                        {#if project.archived}
                            <div class="dropdown-item" on:click={() => unarchiveProjectWithConfirm(project)}>
                                <i class="iconoir-repository" />
                                <span class="txt">Unarchive</span>
                            </div>
                        {:else}
                            <div class="dropdown-item" on:click={() => archiveProjectWithConfirm(project)}>
                                <i class="iconoir-repository" />
                                <span class="txt">Archive</span>
                            </div>
                        {/if}

                        <hr />

                        <div
                            class="dropdown-item link-danger"
                            on:click={() => deleteProjectWithConfirm(project)}
                        >
                            <i class="iconoir-trash" />
                            <span class="txt">Delete</span>
                        </div>
                    </Toggler>
                </button>
            </div>
        </div>
    </figure>
    <div class="card-content">
        <InlineTitleEdit tag="h5" class="title" collection="projects" bind:model={project} />

        <div class="meta">
            {#if totalUnread}
                <div class="meta-item txt-warning" use:tooltip={"Unread comments"}>
                    <i class="iconoir-message-text" />
                    <span class="txt">{totalUnread}</span>
                </div>
            {/if}

            {#if project.archived}
                <div class="meta-item" use:tooltip={"Archived"}>
                    <i class="iconoir-repository" />
                </div>
            {/if}

            <div class="meta-item" use:tooltip={"Created " + utils.relativeDate(project.created)}>
                <div class="iconoir-calendar" />
            </div>
        </div>
    </div>
</div>
