<script>
    import pb, { createLinkClient } from "@/pb";
    import tooltip from "@/actions/tooltip";
    import { addErrorToast } from "@/stores/toasts";
    import { activeProject, addProject, resetProjectsStore } from "@/stores/projects";
    import { prototypes, activePrototype, resetPrototypesStore } from "@/stores/prototypes";
    import { activeScreen, mode, modes } from "@/stores/screens";
    import { selectedComment } from "@/stores/comments";
    import Layout from "@/components/base/Layout.svelte";
    import Field from "@/components/base/Field.svelte";
    import LinkPreview from "@/components/links/LinkPreview.svelte";

    export let params;

    let link;
    let password = " ";
    let showForm = false;
    let isSubmitting = true;

    $: pageTitle = [
        $activeScreen ? "Preview " + $activeScreen.title : null,
        $activePrototype?.title,
        $activeProject?.title,
    ]
        .filter(Boolean)
        .join(" - ");

    load();

    async function load() {
        isSubmitting = true;

        resetProjectsStore();
        resetPrototypesStore();

        const linkClient = createLinkClient(params.groups.linkSlug);

        try {
            const options = {
                expand: "project.users,project.prototypes_via_project",
            };

            if (
                linkClient.authStore.isValid &&
                linkClient.authStore.model?.collectionName == "links" &&
                linkClient.authStore.model?.username == params.groups.linkSlug
            ) {
                try {
                    // try direct retrieval since authRefresh is faster
                    const result = await linkClient.collection("links").authRefresh(options);
                    link = result.record;
                } catch (_) {
                    linkClient.authStore.clear();

                    // fallback to "guest" login
                    const result = await linkClient
                        .collection("links")
                        .authWithPassword(params.groups.linkSlug, password, options);
                    link = result.record;
                }
            } else {
                // try with guest login
                const result = await linkClient
                    .collection("links")
                    .authWithPassword(params.groups.linkSlug, password, options);
                link = result.record;
            }

            // replace the default pb store with the one from the link
            if (!pb.authStore.isValid || !link.expand?.project?.users?.includes(pb.authStore.model?.id)) {
                pb.initStore(params.groups.linkSlug);
            }

            addProject(link.expand?.project);

            $prototypes = link.expand?.project?.expand?.prototypes_via_project || [];
            if (!!$prototypes.find((p) => p.id == params.groups.prototypeId)) {
                $activePrototype = params.groups.prototypeId;
            } else {
                $activePrototype = $prototypes[$prototypes.length - 1]?.id;
            }

            showForm = false;
            isSubmitting = false;
        } catch (err) {
            if (err.isAbort) {
                return;
            }

            password = "";

            isSubmitting = false;

            if (showForm) {
                addErrorToast("Invalid password.");
            } else {
                showForm = true;
            }
        }
    }
</script>

<Layout
    fullpage
    class="screen-preview-layout screen-preview-mode-{$mode} {$mode == modes.comments && $selectedComment
        ? 'screen-preview-comment-popover-active'
        : ''}"
    header={false}
    footer={false}
    title={pageTitle}
>
    {#if showForm}
        <div class="flex-fill" />
        <div class="wrapper wrapper-sm m-b-base entrance-top">
            <div class="panel">
                <h4 class="m-b-base">The project link is password protected</h4>

                <form class="panel-content" on:submit|preventDefault={() => load()}>
                    <Field class="form-field form-field-lg" name="password" let:uniqueId>
                        <div class="field-group m-b-10">
                            <label
                                for={uniqueId}
                                class="addon prefix"
                                use:tooltip={{ position: "left", text: "Password" }}
                            >
                                <i class="iconoir-lock" />
                            </label>
                            <!-- svelte-ignore a11y-autofocus -->
                            <input
                                required
                                autofocus
                                type="password"
                                id={uniqueId}
                                placeholder="Password"
                                bind:value={password}
                            />
                        </div>
                    </Field>

                    <button
                        type="submit"
                        class="btn btn-block btn-lg btn-primary btn-next"
                        class:btn-disabled={isSubmitting}
                        class:btn-loading={isSubmitting}
                    >
                        <span class="txt">View project</span>
                        <i class="iconoir-arrow-right" />
                    </button>
                </form>
            </div>
        </div>
        <div class="flex-fill" />
    {:else if isSubmitting}
        <span class="loader m-t-auto m-b-auto" />
    {:else if !$prototypes.length}
        <div class="wrapper wrapper-sm m-t-auto m-b-auto">
            <div class="placeholder-block">
                <figure class="icon">
                    <i class="iconoir-pen-tablet" />
                </figure>
                <h5 class="title txt-hint">The project doesn't have any prototypes yet.</h5>
            </div>
        </div>
    {:else}
        <LinkPreview {link} params={params.groups} />
    {/if}
</Layout>
