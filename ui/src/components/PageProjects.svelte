<script>
    import Layout from "@/components/base/Layout.svelte";
    import SearchBar from "@/components/base/SearchBar.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import ProjectsList from "@/components/projects/ProjectsList.svelte";
    import ProjectUpsertPanel from "@/components/projects/ProjectUpsertPanel.svelte";

    const pageTitle = "Projects";

    const optionsList = [
        { label: "Active projects", value: false },
        { label: "Archived projects", value: true },
    ];

    let upsertPanel;
    let search = "";
    let archived = false;
</script>

<Layout title={pageTitle}>
    <svelte:fragment slot="header-left">
        <nav class="breadcrumbs">
            <div class="breadcrumb-item">{pageTitle}</div>
        </nav>
    </svelte:fragment>

    <svelte:fragment slot="header-right">
        <button type="button" class="btn btn-primary" on:click={() => upsertPanel?.show()}>
            <i class="iconoir-plus" />
            <div class="txt">New project</div>
        </button>
    </svelte:fragment>

    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <SearchBar placeholder="Search projects..." bind:value={search}>
        <svelte:fragment slot="suffix">
            <ObjectSelect
                class="projects-filter-select fade m-l-xs"
                items={optionsList}
                bind:keyOfSelected={archived}
            />
        </svelte:fragment>
    </SearchBar>

    <ProjectsList bind:search bind:archived />
</Layout>

<ProjectUpsertPanel bind:this={upsertPanel} />

<style lang="scss">
    :global(.projects-filter-select .options-dropdown) {
        min-width: 160px;
    }
</style>
