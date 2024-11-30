<script>
    import { onDestroy } from "svelte";
    import { link, replace } from "svelte-spa-router";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import { resetProjectsStore, activeProject, isLoadingProjects, addProject } from "@/stores/projects";
    import {
        resetPrototypesStore,
        activePrototype,
        isLoadingPrototypes,
        initPrototypesSubscription,
        unsubscribePrototypesFunc,
    } from "@/stores/prototypes";
    import Layout from "@/components/base/Layout.svelte";
    import InlineTitleEdit from "@/components/base/InlineTitleEdit.svelte";
    import PrototypesList from "@/components/prototypes/PrototypesList.svelte";
    import ScreensList from "@/components/screens/ScreensList.svelte";
    import ProjectOwnersPanel from "@/components/projects/ProjectOwnersPanel.svelte";
    import LinksListPanel from "@/components/links/LinksListPanel.svelte";

    export let params;

    let ownersPanel;
    let linksPanel;
    let pageTitle = "";
    let lastProjectId;
    let initialPrototypeId;

    $: if (lastProjectId != params.projectId) {
        lastProjectId = params.projectId;
        initialPrototypeId = params.prototypeId;
        loadProject(lastProjectId);
    }

    $: if ($activeProject?.title) {
        pageTitle = $activeProject.title;
    }

    $: isLoading = $isLoadingProjects || $isLoadingPrototypes;

    $: if ($activePrototype?.id) {
        replace(`/projects/${$activePrototype.project}/prototypes/${$activePrototype.id}`);
    }

    async function loadProject(projectId) {
        resetProjectsStore();
        resetPrototypesStore();
        initPrototypesSubscription(projectId);

        $isLoadingProjects = true;

        try {
            const project = await pb.collection("projects").getOne(projectId);

            addProject(project, true);

            $isLoadingProjects = false;
        } catch (err) {
            if (!err.isAbort) {
                $isLoadingProjects = false;

                pb.error(err);

                if (err.status == 404) {
                    replace(`/`);
                }
            }
        }
    }

    onDestroy(() => {
        unsubscribePrototypesFunc?.();
    });
</script>

<Layout title={pageTitle}>
    <svelte:fragment slot="header-left">
        <nav class="breadcrumbs">
            <a href="/projects" class="breadcrumb-item" use:link>Projects</a>
            {#if $activeProject?.id}
                <div class="breadcrumb-item entrance-left">
                    <InlineTitleEdit collection="projects" bind:model={$activeProject} />
                </div>
            {:else}
                <div class="breadcrumb-item" />
            {/if}
        </nav>

        {#if $activeProject?.id}
            <div class="header-menu entrance-left">
                <button
                    type="button"
                    class="menu-item"
                    use:tooltip={"Project owners"}
                    on:click={() => ownersPanel?.show()}
                >
                    <i class="iconoir-user-plus" />
                </button>
                <button
                    type="button"
                    class="menu-item"
                    use:tooltip={"Share"}
                    on:click={() => linksPanel?.show()}
                >
                    <i class="iconoir-internet txt-base" />
                </button>
            </div>
        {/if}
    </svelte:fragment>

    <svelte:fragment slot="header-right">
        {#if $activePrototype?.id}
            <label for="screen_uploader" class="btn btn-primary btn-screen-upload entrance-right">
                <i class="iconoir-upload-square" />
                <span class="txt">Upload screens</span>
            </label>
        {/if}
    </svelte:fragment>

    {#if isLoading}
        <div class="block txt-center">
            <span class="loader loader-lg" />
        </div>
    {/if}

    <div class="screens-list-wrapper" class:hidden={isLoading || !$activeProject?.id}>
        <PrototypesList projectId={params.projectId} {initialPrototypeId} />

        <ScreensList />
    </div>
</Layout>

<ProjectOwnersPanel bind:this={ownersPanel} />
<LinksListPanel bind:this={linksPanel} />

<style lang="scss">
    .screens-list-wrapper {
        display: flex;
        height: 100%;
        flex-direction: column;
    }
    :global(.breadcrumb-item .inline-edit) {
        max-width: 300px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        @media (max-width: 980px) {
            max-width: 230px;
        }
        @media (max-width: 900px) {
            max-width: 350px;
        }
        @media (max-width: 650px) {
            max-width: 230px;
        }
        @media (max-width: 500px) {
            max-width: 150px;
        }
    }
    @media (max-width: 900px) {
        .breadcrumbs {
            width: calc(100% - 80px);
        }
    }
</style>
