<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import Toggler from "@/components/base/Toggler.svelte";
    import Field from "@/components/base/Field.svelte";
    import { setErrors, errors } from "@/stores/errors";
    import { activeScreen, screens, addScreen, replaceScreenWithConfirm } from "@/stores/screens";
    import { addSuccessToast } from "@/stores/toasts";

    const dispatch = createEventDispatcher();

    const alignments = {
        left: "Left",
        center: "Center",
        right: "Right",
    };

    let panel;
    let formElem;
    let fixedHeaderInput;
    let fixedFooterInput;
    let isSaving = false;
    let togglerActive = false;
    let hasFixedHeader = false;
    let hasFixedFooter = false;
    let isReplacing = false;
    let debounceTimeoutId;

    $: hasErrors = !utils.isEmpty($errors);

    $: if (!hasFixedHeader) {
        $activeScreen.fixedHeader = 0;
    }

    $: if (!hasFixedFooter) {
        $activeScreen.fixedFooter = 0;
    }

    export function show() {
        panel?.show();
    }

    export function hide() {
        panel?.hide();
    }

    refreshFixedToggles();

    function onShow() {
        setErrors({});

        isReplacing = false;

        refreshFixedToggles();
    }

    function onHide() {
        if (hasErrors || ($activeScreen.id && formElem && !formElem.reportValidity())) {
            show();
        }
    }

    function refreshFixedToggles() {
        hasFixedHeader = $activeScreen.fixedHeader > 0;
        hasFixedFooter = $activeScreen.fixedFooter > 0;
    }

    function debounceSave() {
        clearTimeout(debounceTimeoutId);
        debounceTimeoutId = setTimeout(save, 100);
    }

    async function save() {
        if (!formElem?.reportValidity()) {
            return;
        }

        isSaving = true;

        try {
            const updatedScreen = await pb.collection("screens").update($activeScreen.id, $activeScreen);

            addScreen(updatedScreen);

            dispatch("save", updatedScreen);

            // don't trigger to avoid the layout shifts due to the async operations
            // refreshFixedToggles();

            isSaving = false;
        } catch (err) {
            show();

            if (!err?.isAbort) {
                isSaving = false;
                pb.error(err);
            }
        }
    }

    async function bulkUpdateProp(prop, value) {
        isSaving = true;

        const data = {};
        data[prop] = value;

        try {
            const promises = [];

            for (let screen of $screens) {
                promises.push(
                    pb.collection("screens").update(screen.id, data, {
                        requestKey: "bulk_update_prop_" + prop + screen.id,
                    })
                );
            }

            await Promise.all(promises).then((updatedScreens) => {
                for (let screen of updatedScreens) {
                    addScreen(screen);
                }
            });

            addSuccessToast("Successfully bulk applied the screen setting.");

            isSaving = false;
        } catch (err) {
            if (!err?.isAbort) {
                isSaving = false;
                pb.error(err);
            }
        }
    }
</script>

<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<div
    tabindex="0"
    class="btn btn-circle btn-hint txt-hint btn-transparent btn-screen-settings"
    use:tooltip={!togglerActive ? { position: "top", text: "Screen settings" } : undefined}
>
    {#if isSaving}
        <div class="loader loader-xs" />
    {:else}
        <i class="iconoir-settings" />
    {/if}

    <Toggler
        bind:this={panel}
        class="screen-settings"
        bind:active={togglerActive}
        focusHide={!isReplacing}
        on:show={onShow}
        on:hide={onHide}
    >
        <!-- svelte-ignore a11y-click-events-have-key-events -->
        <!-- svelte-ignore a11y-no-noninteractive-element-interactions -->
        <form
            bind:this={formElem}
            class="grid grid-sm"
            on:change={debounceSave}
            on:submit|preventDefault={save}
            on:click|stopPropagation
        >
            <div class="col-12">
                <Field class="form-field required" name="title" let:uniqueId>
                    <input
                        type="text"
                        id={uniqueId}
                        required
                        placeholder="Screen title"
                        bind:value={$activeScreen.title}
                    />
                </Field>
            </div>
            <div class="col-6">
                <Field class="form-field form-field-sm" name="alignment" let:uniqueId>
                    <!-- svelte-ignore a11y-label-has-associated-control -->
                    <label>
                        <span class="txt">Alignment</span>
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <i
                            class="iconoir-keyframes txt-sm {isSaving ? 'txt-disabled' : 'link-hint'}"
                            class:no-pointer-events={isSaving}
                            use:tooltip={"Apply to all screens"}
                            on:click|preventDefault|stopPropagation={() => {
                                bulkUpdateProp("alignment", $activeScreen.alignment);
                            }}
                        />
                    </label>
                    <div class="btns-group btns-group-block">
                        {#each Object.entries(alignments) as [key, label]}
                            <button
                                type="button"
                                class="btn btn-sm btn-semitransparent"
                                class:btn-primary={$activeScreen.alignment == key ||
                                    (!$activeScreen.alignment && key == "center")}
                                on:click|preventDefault={() => {
                                    $activeScreen.alignment = key;
                                    save();
                                }}
                            >
                                {label}
                            </button>
                        {/each}
                    </div>
                </Field>
            </div>
            <div class="col-6">
                <Field class="form-field form-field-sm" name="background" let:uniqueId>
                    <label for={uniqueId}>
                        <span class="txt">Background</span>
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <i
                            class="iconoir-keyframes txt-sm {isSaving ? 'txt-disabled' : 'link-hint'}"
                            class:no-pointer-events={isSaving}
                            use:tooltip={"Apply to all screens"}
                            on:click|preventDefault|stopPropagation={() => {
                                bulkUpdateProp("background", $activeScreen.background);
                            }}
                        />
                    </label>
                    <div class="field-group">
                        <input
                            type="color"
                            id={uniqueId}
                            class:fade={!$activeScreen.background}
                            bind:value={$activeScreen.background}
                        />
                        <label for={uniqueId} class="addon txt-mono"
                            >{$activeScreen.background || "none"}</label
                        >
                    </div>
                </Field>
            </div>
            <div class="col-6 flex-align-self-center">
                <Field class="form-field form-field-sm form-field-toggle" let:uniqueId>
                    <input
                        type="checkbox"
                        id={uniqueId}
                        bind:checked={hasFixedHeader}
                        on:change={(e) => {
                            if (e.target.checked) {
                                e.stopPropagation();
                            }
                            fixedHeaderInput?.select();
                        }}
                    />
                    <label for={uniqueId}>Has fixed header</label>
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <i
                        class="iconoir-keyframes txt-sm {isSaving ? 'txt-disabled' : 'link-hint'}"
                        class:no-pointer-events={isSaving}
                        use:tooltip={"Apply to all screens"}
                        on:click|preventDefault|stopPropagation={() =>
                            bulkUpdateProp("fixedHeader", $activeScreen.fixedHeader)}
                    />
                </Field>
            </div>
            <div class="col-6">
                <Field
                    class="form-field form-field-sm form-field-sm {!hasFixedHeader ? 'invisible' : ''}"
                    name="fixedHeader"
                    let:uniqueId
                >
                    <div class="field-group">
                        <input
                            bind:this={fixedHeaderInput}
                            id={uniqueId}
                            type="number"
                            min="0"
                            bind:value={$activeScreen.fixedHeader}
                        />
                        <div class="addon">px</div>
                    </div>
                </Field>
            </div>
            <div class="col-6 flex-align-self-center">
                <Field class="form-field form-field-sm form-field-toggle" let:uniqueId>
                    <input
                        type="checkbox"
                        id={uniqueId}
                        bind:checked={hasFixedFooter}
                        on:change={(e) => {
                            if (e.target.checked) {
                                e.stopPropagation();
                            }
                            fixedFooterInput?.select();
                        }}
                    />
                    <label for={uniqueId}>Has fixed footer</label>
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <i
                        class="iconoir-keyframes txt-sm {isSaving ? 'txt-disabled' : 'link-hint'}"
                        class:no-pointer-events={isSaving}
                        use:tooltip={"Apply to all screens"}
                        on:click|preventDefault|stopPropagation={() =>
                            bulkUpdateProp("fixedFooter", $activeScreen.fixedFooter)}
                    />
                </Field>
            </div>
            <div class="col-6">
                <Field
                    class="form-field form-field-sm form-field-sm {!hasFixedFooter ? 'invisible' : ''}"
                    name="fixedFooter"
                    let:uniqueId
                >
                    <div class="field-group">
                        <input
                            bind:this={fixedFooterInput}
                            id={uniqueId}
                            type="number"
                            min="0"
                            bind:value={$activeScreen.fixedFooter}
                        />
                        <div class="addon">px</div>
                    </div>
                </Field>
            </div>

            <hr />

            <div class="block">
                <Field class="block" name="file" let:uniqueId>
                    <input
                        type="file"
                        id={uniqueId}
                        class="hidden"
                        on:change={(e) => {
                            if (e.target.files.length) {
                                isReplacing = true;
                                replaceScreenWithConfirm($activeScreen, e.target.files[0]).finally(() => {
                                    isReplacing = false;
                                });
                            }
                        }}
                    />
                    <label for={uniqueId} class="link-hint txt-sm">Replace screen</label>
                </Field>
            </div>
        </form>
    </Toggler>
</div>
