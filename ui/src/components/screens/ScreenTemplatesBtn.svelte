<script>
    import { createEventDispatcher, onDestroy } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { confirm } from "@/stores/confirmation";
    import { activeScreen } from "@/stores/screens";
    import { hotspots } from "@/stores/hotspots";
    import { templates, activeScreenTemplates, addTemplate, removeTemplate } from "@/stores/templates";
    import tooltip from "@/actions/tooltip";
    import Toggler from "@/components/base/Toggler.svelte";
    import Field from "@/components/base/Field.svelte";

    const dispatch = createEventDispatcher();

    let isDeleting = false;
    let debounceTimeoutId = null;

    function debounceSaveTemplate(template) {
        clearTimeout(debounceTimeoutId);
        debounceTimeoutId = setTimeout(() => {
            saveTemplate(template);
        }, 100);
    }

    async function saveTemplate(template) {
        if (!template?.id) {
            return;
        }

        // optimistick update
        addTemplate(template);

        try {
            template = await pb.collection("hotspotTemplates").update(template.id, template);

            dispatch("save", template);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    function deleteWithConfirm(template) {
        if (isDeleting || !template?.id) {
            return;
        }

        let message = `Do you really want to delete template "${template.title}" and all of its hotspots?`;

        // eagerly set to prevent popup close
        isDeleting = true;

        confirm(
            message,
            () => deleteTemplate(template),
            () => {
                // reorder exec queue
                setTimeout(() => {
                    isDeleting = false;
                }, 0);
            }
        );
    }

    async function deleteTemplate(template) {
        if (!template?.id) {
            return;
        }

        isDeleting = true;

        try {
            await pb.collection("hotspotTemplates").delete(template.id);

            dispatch("delete", template);

            removeTemplate(template);

            isDeleting = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isDeleting = false;
            }
        }
    }

    async function newTemplate() {
        // generate a template title
        let basePrefix = "Template ";
        let templateTitle = "";
        for (let i = 1; i < 1000; i++) {
            templateTitle = basePrefix + ($templates.length + i);
            if (!$templates.find((t) => t.title == templateTitle)) {
                break;
            }
        }

        let template = {
            id: "auto" + utils.randomString(11),
            prototype: $activeScreen["prototype"],
            screens: [$activeScreen.id],
            title: templateTitle,
        };

        // optimistick create
        addTemplate(template);

        try {
            template = await pb.collection("hotspotTemplates").create(template);

            addTemplate(template);

            dispatch("create", template);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    onDestroy(() => {
        if (debounceTimeoutId) {
            clearTimeout(debounceTimeoutId);
        }
    });
</script>

<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<!-- svelte-ignore a11y-click-events-have-key-events -->
<div tabindex="0" type="button" class="btn btn-sm btn-hint txt-hint btn-transparent entrance-right">
    <strong>{$activeScreenTemplates.length}</strong> Applied hotspot {$activeScreenTemplates.length == 1
        ? "template"
        : "templates"}
    <i class="iconoir-nav-arrow-up" />

    <Toggler class="dropdown templates-dropdown dropdown-upside" focusHide={false} disableHide={isDeleting}>
        {#each $templates as template (template.id)}
            {@const totalHotspots = $hotspots.filter((h) => h.hotspotTemplate == template.id)?.length}
            <div class="dropdown-item" on:click|stopPropagation>
                <Field class="form-field form-field-toggle form-field-auto m-0" let:uniqueId>
                    <input
                        type="checkbox"
                        id={uniqueId}
                        checked={template.screens?.includes($activeScreen.id)}
                        on:change={(e) => {
                            if (e.target.checked) {
                                utils.pushUnique(template.screens, $activeScreen.id);
                            } else {
                                utils.removeByValue(template.screens, $activeScreen.id);
                            }
                            saveTemplate(template);
                        }}
                    />
                    <label for={uniqueId} />
                </Field>

                <span
                    class="template-title"
                    contenteditable="true"
                    data-placeholder={template.title}
                    title="Edit name"
                    on:blur={(e) => {
                        // restore the original title
                        if (!e.target.textContent?.trim()) {
                            e.target.textContent = template.title;
                        }
                    }}
                    on:keydown={(e) => {
                        if (e.code === "Enter") {
                            e.preventDefault();
                            e.target.blur();
                        }
                    }}
                    on:input={(e) => {
                        const txt = e.target.textContent?.trim();
                        if (txt && txt != template.title) {
                            template.title = txt;
                            debounceSaveTemplate(template);
                        }
                    }}>{template.title}</span
                >

                <code class="txt-hint" use:tooltip={"Total hotspots"}>{totalHotspots}</code>

                <button
                    type="button"
                    class="btn btn-circle btn-sm btn-danger btn-transparent m-l-auto link-fade delete-btn"
                    on:click|preventDefault={() => deleteWithConfirm(template)}
                >
                    <i class="iconoir-trash txt-xs" />
                </button>
            </div>
        {/each}

        <div class="block p-5" class:m-t-5={$templates.length}>
            <button
                type="button"
                class="btn btn-block btn-sm btn-success btn-semitransparent"
                on:click={newTemplate}
            >
                <i class="iconoir-plus" />
                <span class="txt">New hotspot template</span>
            </button>
        </div>
    </Toggler>
</div>

<style>
    .delete-btn {
        margin-right: -5px;
    }
</style>
