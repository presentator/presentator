<script>
    import pb from "@/pb";
    import { addProject, projects, isLoadingProjects, resetProjectsStore } from "@/stores/projects";
    import ProjectUpsertPanel from "@/components/projects/ProjectUpsertPanel.svelte";
    import ProjectCard from "@/components/projects/ProjectCard.svelte";

    const perPage = 50;

    export let search = "";
    export let archived = false;

    let upsertPanel;
    let currentPage = 1;
    let lastFetched = 0;
    let loadTimeoutId;

    $: canLoadMore = lastFetched == perPage;

    // reload on search or archived changes
    $: if (search !== -1 || archived !== -1) {
        $isLoadingProjects = true; // force show the loader
        clearTimeout(loadTimeoutId);
        loadTimeoutId = setTimeout(() => load(1), 50);
    }

    $: hasResetableFilters = search || archived;

    $: favoriteProjects = $projects.filter((p) => p.expand?.projectUserPreferences_via_project?.[0].favorite);

    $: nonFavoriteProjects = $projects.filter(
        (p) => !p.expand?.projectUserPreferences_via_project?.[0].favorite,
    );

    resetList();

    async function load(page = 1) {
        if (page <= 1) {
            resetList();
        }

        $isLoadingProjects = true;

        let loadFilter = `project.archived=${archived}`;
        if (search) {
            loadFilter += pb.filter("&&project.title~{:title}", { title: search });
        }

        // @todo change to fetch from project once back relations are introduced
        return pb
            .collection("projectUserPreferences")
            .getList(page, perPage, {
                sort: "-favorite,-lastVisited,-project.created",
                filter: loadFilter,
                skipTotal: 1,
                expand: "project.prototypes_via_project.screens_via_prototype",
                requestKey: "projects_list",
            })
            .then((result) => {
                currentPage = result.page;
                lastFetched = result.items.length;

                for (const item of result.items) {
                    // remap the preferences as project expand
                    const project = item.expand?.project;
                    if (!project) {
                        console.warn("missing project for preference", item.id);
                        continue;
                    }
                    delete item.expand;
                    project.expand = project.expand || {};
                    project.expand.projectUserPreferences_via_project = [item];

                    addProject(project);
                }

                $isLoadingProjects = false;
            })
            .catch((err) => {
                if (!err?.isAbort) {
                    $isLoadingProjects = false;
                    pb.error(err);
                }
            });
    }

    function resetList() {
        resetProjectsStore();
        lastFetched = 0;
        currentPage = 1;
    }

    function resetFilters() {
        search = "";
        archived = false;
    }
</script>

{#if $isLoadingProjects && !$projects.length}
    <div class="block txt-center m-t-lg">
        <span class="loader loader-lg" />
    </div>
{:else if !$isLoadingProjects && !$projects.length}
    {#if hasResetableFilters}
        <div class="placeholder-block m-b-sm entrance-top">
            <div class="icon">
                <i class="iconoir-grid-xmark" />
            </div>
            <h6 class="title">No projects found!</h6>
        </div>
    {:else}
        <div class="placeholder-block entrance-top">
            <div class="icon">
                <i class="iconoir-grid-plus" />
            </div>
            <h6 class="title">You don't have any active projects yet!</h6>
            <div class="content">
                <p>Create a new project to share your designs, collect feedback and more.</p>
            </div>
            <button
                type="button"
                class="btn btn-expanded btn-primary btn-lg m-t-base"
                on:click={() => upsertPanel?.show()}
            >
                <i class="iconoir-plus" />
                <span class="txt">New project</span>
            </button>
        </div>
    {/if}
{:else}
    {#if favoriteProjects.length}
        <div class="cards-list">
            {#each favoriteProjects as project (project.id)}
                <ProjectCard bind:project />
            {/each}
        </div>
    {/if}

    {#if favoriteProjects.length && nonFavoriteProjects.length}
        <hr />
    {/if}

    {#if nonFavoriteProjects.length}
        <div class="cards-list">
            {#each nonFavoriteProjects as project (project.id)}
                <ProjectCard bind:project />
            {/each}
        </div>
    {/if}
{/if}

<div class="block txt-center m-t-base">
    {#if canLoadMore}
        <button
            type="button"
            class="btn btn-semitransparent btn-expanded btn-lg m-b-sm entrance-top"
            class:btn-disabled={$isLoadingProjects}
            class:btn-loading={$isLoadingProjects}
            on:click={() => load(currentPage + 1)}
        >
            <span class="txt">Load more</span>
        </button>

        <div class="clearfix" />
    {/if}

    {#if !$isLoadingProjects && hasResetableFilters}
        <button
            type="button"
            class="btn btn-transparent btn-hint btn-expanded entrance-top"
            on:click={resetFilters}
        >
            Reset filters
        </button>
    {/if}
</div>

<ProjectUpsertPanel bind:this={upsertPanel} />
