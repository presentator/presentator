<script>
    import { createEventDispatcher } from "svelte";
    import { push } from "svelte-spa-router";
    import pb from "@/pb";
    import utils from "@/utils";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";
    import PrototypeTypeField from "@/components/prototypes/PrototypeTypeField.svelte";
    import { setErrors } from "@/stores/errors";

    const formId = "project_upsert_" + utils.randomString(5);

    const dispatch = createEventDispatcher();

    export let successRedirect = true;

    let panel;
    let project = {};
    let prototype = {};
    let isSubmitting = false;

    export function show(projectData) {
        setErrors({});

        project = Object.assign(
            {
                users: [pb.authStore.record.id],
            },
            projectData,
        );

        prototype = {};
        if (!project.id) {
            prototype.size = "";
            prototype.scale = 1;
        }

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    async function submit() {
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;

        try {
            let result;
            if (project.id) {
                result = await pb.collection("projects").update(project.id, project);
            } else {
                result = await pb.collection("projects").create(project);

                // auto create the first prototype
                await pb.collection("prototypes").create(
                    Object.assign(
                        {
                            project: result.id,
                            title: utils.isDesktopPrototype(prototype) ? "Desktop" : "Mobile",
                        },
                        prototype,
                    ),
                );
            }

            dispatch("submit", result);

            isSubmitting = false;

            if (successRedirect) {
                push("/projects/" + result.id + "/prototypes");
            } else {
                hide();
            }
        } catch (err) {
            if (!err.isAbort) {
                isSubmitting = false;
                pb.error(err);
            }
        }
    }
</script>

<OverlayPanel
    bind:this={panel}
    popup
    overlayClose={!isSubmitting}
    escHide={!isSubmitting}
    beforeHide={() => !isSubmitting}
    on:show
    on:hide
>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">
            {project.id ? "Update project" : "Create project"}
        </h5>
    </svelte:fragment>

    <form id={formId} class="content" on:submit|preventDefault={submit}>
        <Field class="form-field required" name="title" let:uniqueId>
            <label for={uniqueId}>Project title</label>
            <!-- svelte-ignore a11y-autofocus -->
            <input type="text" id={uniqueId} autofocus={true} required bind:value={project.title} />
        </Field>

        {#if !project.id}
            <div class="block m-b-10">Prototype</div>
            <PrototypeTypeField bind:prototype />
        {/if}
    </form>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent" disabled={isSubmitting} on:click={() => hide()}>
            Cancel
        </button>
        <button
            type="submit"
            form={formId}
            class="btn btn-expanded btn-primary m-l-auto"
            class:btn-loading={isSubmitting}
            disabled={isSubmitting}
        >
            {project.id ? "Update project" : "Create project"}
        </button>
    </svelte:fragment>
</OverlayPanel>
