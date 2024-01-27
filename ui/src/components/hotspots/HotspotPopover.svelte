<script>
    import { createEventDispatcher, tick } from "svelte";
    import { slide } from "svelte/transition";
    import pb from "@/pb";
    import { confirm } from "@/stores/confirmation";
    import { setErrors } from "@/stores/errors";
    import { activeScreen } from "@/stores/screens";
    import { activePrototype } from "@/stores/prototypes";
    import {
        selectedHotspot,
        isHotspotDragging,
        addHotspot,
        removeUnsavedHotspots,
        hotspotTypes,
    } from "@/stores/hotspots";
    import { options } from "@/stores/app";
    import { templates, addTemplate } from "@/stores/templates";
    import tooltip from "@/actions/tooltip";
    import Popover from "@/components/base/Popover.svelte";
    import Field from "@/components/base/Field.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import ScreenField from "@/components/hotspots/ScreenField.svelte";
    import TransitionField from "@/components/hotspots/TransitionField.svelte";
    import OverlayPositionField from "./OverlayPositionField.svelte";

    const dispatch = createEventDispatcher();

    const baseTypeOptions = [
        { value: hotspotTypes.note, label: "Link to: Note" },
        { value: hotspotTypes.screen, label: "Link to: Screen" },
        { value: hotspotTypes.overlay, label: "Link to: Screen as overlay" },
        { value: hotspotTypes.back, label: "Link to: Back (last visited screen)" },
        { value: hotspotTypes.prev, label: "Link to: Prev screen in series" },
        { value: hotspotTypes.next, label: "Link to: Next screen in series" },
        { value: hotspotTypes.scroll, label: "Link to: Position (scroll to)" },
    ];

    const allTypeOptions = baseTypeOptions.concat({ value: hotspotTypes.url, label: "Link to: URL" });

    const NEW_TEMPLATE_VALUE = "@newTemplate";

    export let viewport = null;

    let popover;
    let isSaving = false;
    let isDeleting = false;
    let newTemplateTitle = "";
    let templateNameInput;
    let originalHotspot = null;
    let typeOptions = baseTypeOptions;
    let oldType = "";

    $: if ($options?.allowHotspotPopover) {
        typeOptions = allTypeOptions;
    } else {
        typeOptions = baseTypeOptions;
    }

    $: if ($activeScreen && originalHotspot?.id != $selectedHotspot?.id) {
        onSelectedHotspotChange();
    }

    $: canHide = !isSaving && !isDeleting;

    $: templateOptions = $templates
        .map((t) => ({ value: t.id, label: t.title }))
        .concat([{ value: NEW_TEMPLATE_VALUE, label: "+ New template" }]);

    $: if ($selectedHotspot?.hotspotTemplate === NEW_TEMPLATE_VALUE) {
        // short delay to ensure that the field is visible
        setTimeout(() => {
            templateNameInput?.focus();
        }, 50);
    }

    $: if ($selectedHotspot && oldType != $selectedHotspot.type) {
        onTypeChange();
    }

    async function onSelectedHotspotChange() {
        if (!$selectedHotspot || $isHotspotDragging) {
            popover?.forceHide();
            return;
        }

        const hotspotElem = document.querySelector(`[data-hotspot="${$selectedHotspot.id}"]`);
        if (!hotspotElem) {
            popover?.forceHide();
            return;
        }

        hotspotElem.scrollIntoView({
            block: "nearest",
            inline: "nearest",
        });

        initPopoverData();

        await tick();

        popover?.show(hotspotElem, viewport);
    }

    function initPopoverData() {
        setErrors({});

        newTemplateTitle = "";
        isSaving = false;
        isDeleting = false;
        oldType = "";

        if ($selectedHotspot) {
            $selectedHotspot.settings = $selectedHotspot.settings || {};
            originalHotspot = cloneSelectedHotspot();
            onTypeChange();
        }
    }

    function cloneSelectedHotspot() {
        if (!$selectedHotspot) {
            return null;
        }

        // use combination of Object.assign and structuredClone because
        // the original hotspot can contain custom events and functions attached to the object
        const clone = Object.assign({}, $selectedHotspot);
        clone.settings = structuredClone($selectedHotspot.settings || {});
        return clone;
    }

    function onTypeChange() {
        if (!$selectedHotspot) {
            return null;
        }

        oldType = $selectedHotspot.type;

        // set default overlay settings
        if (!$selectedHotspot.id && $selectedHotspot.type == hotspotTypes.overlay) {
            $selectedHotspot.settings = $selectedHotspot.settings || {};
            $selectedHotspot.settings.fixOverlay = true;
            $selectedHotspot.settings.outsideClose = true;
        }
    }

    function onHide() {
        // if the selected wasn't removed -> restore the original hotspot state
        if ($selectedHotspot?.id && originalHotspot) {
            addHotspot(originalHotspot);
        }

        originalHotspot = null;
        $selectedHotspot = null;

        removeUnsavedHotspots();
    }

    async function save() {
        if (isSaving || !$selectedHotspot) {
            return;
        }

        isSaving = true;

        try {
            const data = cloneSelectedHotspot();

            if (data.hotspotTemplate) {
                data.screen = null; // unset screen reference

                let foundTemplate = $templates.find((t) => t.id == data.hotspotTemplate);

                if (!foundTemplate) {
                    // create template
                    foundTemplate = await pb.collection("hotspotTemplates").create({
                        title: newTemplateTitle,
                        prototype: $activePrototype.id,
                        screens: [$activeScreen.id],
                    });
                    data.hotspotTemplate = foundTemplate.id;
                } else if (!foundTemplate.screens?.includes($activeScreen.id)) {
                    // associate the current screen with the template
                    foundTemplate = await pb.collection("hotspotTemplates").update(foundTemplate.id, {
                        "screens+": $activeScreen.id,
                    });
                }

                addTemplate(foundTemplate);
            } else {
                data.screen = $activeScreen.id;
            }

            let hotspot;
            if ($selectedHotspot?.id) {
                hotspot = await pb.collection("hotspots").update($selectedHotspot.id, data);
            } else {
                hotspot = await pb.collection("hotspots").create(data);
            }

            originalHotspot = hotspot;

            addHotspot(hotspot);

            removeUnsavedHotspots();

            popover?.forceHide();

            dispatch("save", hotspot);

            isSaving = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSaving = false;
            }
        }
    }

    function deleteWithConfirm() {
        if (isDeleting || !$selectedHotspot?.id) {
            return;
        }

        let message = "Do you really want to delete the selected hotspot?";

        // eagerly set to prevent popup close
        isDeleting = true;

        confirm(message, deleteHotspot, () => {
            // reorder exec queue
            setTimeout(() => {
                isDeleting = false;
            }, 0);
        });
    }

    async function deleteHotspot() {
        if (!$selectedHotspot?.id) {
            return;
        }

        isDeleting = true;

        try {
            await pb.collection("hotspots").delete($selectedHotspot.id);

            dispatch("delete", originalHotspot);

            $selectedHotspot = null;

            isDeleting = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isDeleting = false;
            }
        }
    }
</script>

<Popover
    bind:this={popover}
    class="hotspots-popover {$isHotspotDragging ? 'resizing' : ''}"
    disableHide={!canHide}
    vPadding={0}
    hPadding={5}
    closableToggle={false}
    on:hide={onHide}
    {...$$restProps}
>
    {#if $selectedHotspot}
        <form on:submit|preventDefault={save}>
            <div class="grid grid-sm">
                <Field class="form-field hotspot-type-select" name="type" let:uniqueId>
                    <ObjectSelect
                        id={uniqueId}
                        items={typeOptions}
                        bind:keyOfSelected={$selectedHotspot.type}
                    />
                </Field>

                {#if $selectedHotspot.type == hotspotTypes.note}
                    <Field class="form-field required" name="settings.note" let:uniqueId>
                        <textarea id={uniqueId} required bind:value={$selectedHotspot.settings.note} />
                        <div class="help-block">The note will be visible in Preview mode on hover.</div>
                    </Field>
                {:else if $selectedHotspot.type == hotspotTypes.screen}
                    <ScreenField bind:value={$selectedHotspot.settings.screen} />
                    <TransitionField bind:value={$selectedHotspot.settings.transition} />
                {:else if $selectedHotspot.type == hotspotTypes.overlay}
                    <ScreenField bind:value={$selectedHotspot.settings.screen} />
                    <OverlayPositionField bind:value={$selectedHotspot.settings.overlayPosition} />
                    <Field class="form-field" let:uniqueId>
                        <label for={uniqueId}>Offset <span class="txt-hint">(px)</span></label>
                        <div class="field-group">
                            <label for="{uniqueId}_t" class="addon" title="Top offset">T</label>
                            <input
                                type="number"
                                id="{uniqueId}_t"
                                bind:value={$selectedHotspot.settings.offsetTop}
                            />
                            <label for="{uniqueId}_b" class="addon" title="Bottom offset">B</label>
                            <input
                                type="number"
                                id="{uniqueId}_b"
                                bind:value={$selectedHotspot.settings.offsetBottom}
                            />
                            <label for="{uniqueId}_l" class="addon" title="Left offset">L</label>
                            <input
                                type="number"
                                id="{uniqueId}_l"
                                bind:value={$selectedHotspot.settings.offsetLeft}
                            />
                            <label for="{uniqueId}_r" class="addon" title="Right offset">R</label>
                            <input
                                type="number"
                                id="{uniqueId}_r"
                                bind:value={$selectedHotspot.settings.offsetRight}
                            />
                        </div>
                    </Field>
                    <TransitionField bind:value={$selectedHotspot.settings.transition} />
                    <Field class="form-field form-field-toggle" name="settings.fixOverlay" let:uniqueId>
                        <input
                            type="checkbox"
                            id={uniqueId}
                            bind:checked={$selectedHotspot.settings.fixOverlay}
                        />
                        <label for={uniqueId}>Fix position of overlay</label>
                    </Field>
                    <Field class="form-field form-field-toggle" name="settings.outsideClose" let:uniqueId>
                        <input
                            type="checkbox"
                            id={uniqueId}
                            bind:checked={$selectedHotspot.settings.outsideClose}
                        />
                        <label for={uniqueId}>Close on outside click</label>
                    </Field>
                {:else if $selectedHotspot.type == hotspotTypes.back}
                    <TransitionField bind:value={$selectedHotspot.settings.transition} />
                {:else if $selectedHotspot.type == hotspotTypes.next}
                    <TransitionField bind:value={$selectedHotspot.settings.transition} />
                {:else if $selectedHotspot.type == hotspotTypes.prev}
                    <TransitionField bind:value={$selectedHotspot.settings.transition} />
                {:else if $selectedHotspot.type == hotspotTypes.url}
                    <Field class="form-field required" name="settings.url" let:uniqueId>
                        <label for={uniqueId}>URL</label>
                        <div class="field-group">
                            <input
                                type="text"
                                id={uniqueId}
                                required
                                placeholder="eg. https://google.com"
                                bind:value={$selectedHotspot.settings.url}
                            />
                        </div>
                    </Field>
                {:else if $selectedHotspot.type == hotspotTypes.scroll}
                    <div class="col-6">
                        <Field class="form-field required" name="settings.scrollTop" let:uniqueId>
                            <label for={uniqueId}>Vertical position</label>
                            <div class="field-group">
                                <input
                                    type="number"
                                    id={uniqueId}
                                    min="0"
                                    required
                                    bind:value={$selectedHotspot.settings.scrollTop}
                                />
                                <label for={uniqueId} class="addon">px</label>
                            </div>
                        </Field>
                    </div>
                    <div class="col-6">
                        <Field class="form-field required" name="settings.scrollLeft" let:uniqueId>
                            <label for={uniqueId}>Horizontal position</label>
                            <div class="field-group">
                                <input
                                    type="number"
                                    id={uniqueId}
                                    min="0"
                                    required
                                    bind:value={$selectedHotspot.settings.scrollLeft}
                                />
                                <label for={uniqueId} class="addon">px</label>
                            </div>
                        </Field>
                    </div>
                {/if}

                <!-- template -->
                <div class="col-12">
                    <Field class="form-field form-field-toggle" name="template" let:uniqueId>
                        <input
                            type="checkbox"
                            id={uniqueId}
                            checked={!!$selectedHotspot.hotspotTemplate}
                            on:change={(e) => {
                                if (e.target.checked) {
                                    if (!$selectedHotspot.hotspotTemplate) {
                                        $selectedHotspot.hotspotTemplate =
                                            originalHotspot?.hotspotTemplate || templateOptions?.[0]?.value;
                                    }
                                } else {
                                    $selectedHotspot.hotspotTemplate = "";
                                }
                            }}
                        />
                        <label for={uniqueId}>
                            <span class="txt">Include in template</span>
                            <i
                                class="iconoir-info-circle link-hint"
                                use:tooltip={"Reuse the hotspot in other screens"}
                            />
                        </label>
                    </Field>

                    {#if $selectedHotspot.hotspotTemplate}
                        <div class="block p-t-xs" transition:slide={{ duration: 150 }}>
                            <div class="template-form">
                                <Field class="form-field form-field-sm" let:uniqueId>
                                    <ObjectSelect
                                        id={uniqueId}
                                        items={templateOptions}
                                        upside
                                        bind:keyOfSelected={$selectedHotspot.hotspotTemplate}
                                    />
                                </Field>
                                {#if $selectedHotspot.hotspotTemplate == NEW_TEMPLATE_VALUE}
                                    <div transition:slide={{ duration: 150 }}>
                                        <Field
                                            class="form-field form-field-sm m-t-10"
                                            name="title"
                                            let:uniqueId
                                        >
                                            <input
                                                bind:this={templateNameInput}
                                                type="text"
                                                id={uniqueId}
                                                placeholder="Template name *"
                                                bind:value={newTemplateTitle}
                                            />
                                        </Field>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            <hr />

            <footer class="flex flex-gap-10">
                <button
                    type="button"
                    class="btn btn-sm btn-transparent"
                    disabled={isSaving || isDeleting}
                    on:click|stopPropagation={() => {
                        popover?.hide();
                    }}
                >
                    <span class="txt">Cancel</span>
                </button>

                {#if $selectedHotspot?.id}
                    <button
                        type="button"
                        class="btn btn-sm btn-circle btn-transparent btn-danger fade"
                        disabled={isSaving || isDeleting}
                        use:tooltip={"Delete hotspot"}
                        on:click|stopPropagation={deleteWithConfirm}
                    >
                        <i class="iconoir-trash txt-sm" />
                    </button>
                {/if}

                <button
                    type="submit"
                    class="btn btn-sm btn-primary btn-expanded-sm m-l-auto"
                    class:btn-loading={isSaving}
                    disabled={isSaving || isDeleting}
                >
                    <span class="txt">{$selectedHotspot.id ? "Save" : "Create"}</span>
                </button>
            </footer>
        </form>
    {/if}
</Popover>

<style lang="scss">
    .template-form {
        display: block;
        width: 100%;
        border-radius: var(--baseRadius);
        padding: var(--xsSpacing);
        border: 1px solid var(--baseAlt1Color);
    }
</style>
