<script>
    import { createEventDispatcher } from "svelte";
    import { slide } from "svelte/transition";
    import pb from "@/pb";
    import utils from "@/utils";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import { prototypes } from "@/stores/prototypes";
    import tooltip from "@/actions/tooltip";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Field from "@/components/base/Field.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";

    const dispatch = createEventDispatcher();
    const formId = "link_upsert_" + utils.randomString(5);

    export let active = false;

    let panel;
    let link = {};
    let restrictPrototypes = false;
    let changePassword = false;
    let password = "";
    let isSaving = false;
    let initialPasswordProtect = false;

    $: url = utils.getProjectLinkURL(link);

    $: prototypesList =
        $prototypes.map((item) => {
            return { value: item.id, label: item.title };
        }) || [];

    export function show(upsert) {
        link = structuredClone(upsert);

        reset();

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    function reset() {
        setErrors({});

        restrictPrototypes = link.onlyPrototypes?.length > 0;
        initialPasswordProtect = !!link.passwordProtect;
        changePassword = !link.id || !initialPasswordProtect;
        password = "";
        isSaving = false;
    }

    async function save() {
        isSaving = true;

        try {
            const isNew = !link.id;

            const data = {
                project: link.project,
                allowComments: link.allowComments,
                onlyPrototypes: restrictPrototypes ? link.onlyPrototypes : [],
                passwordProtect: link.passwordProtect,
            };

            if (isNew && !data.passwordProtect) {
                // set a random password
                // (the value doesn't matter in this case as the password would be ignored)
                data.password = "pb123456";
                data.passwordConfirm = data.password;
            } else if (data.passwordProtect && changePassword) {
                data.password = password;
                data.passwordConfirm = data.password;
            }

            if (isNew) {
                link = await pb.collection("links").create(data);
                addSuccessToast("Successfully created new project link.");
            } else {
                link = await pb.collection("links").update(link.id, data);
                addSuccessToast("Successfully updated project link.");
            }

            isSaving = false;

            hide();

            dispatch("save", link);
        } catch (err) {
            isSaving = false;
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }
</script>

<OverlayPanel
    bind:this={panel}
    bind:active
    popup
    on:show
    on:hide
    beforeHide={() => !isSaving}
    {...$$restProps}
>
    <svelte:fragment slot="header">
        <div class="flex-fill">
            <button
                type="button"
                class="btn btn-circle btn-sm btn-transparent btn-hint"
                use:tooltip={{ position: "right", text: "Back" }}
                on:click={hide}
            >
                <i class="iconoir-arrow-left" />
            </button>
        </div>
        <h5 class="title">{link.id ? "Update link" : "Create link"}</h5>
        <div class="flex-fill">&nbsp;</div>
    </svelte:fragment>

    <form id={formId} class="grid" on:submit|preventDefault={save}>
        {#if link.id}
            <div class="col-12">
                <div class="alert alert-warning m-0">
                    <div class="content txt-center txt-bold">
                        <a
                            href={url}
                            target="_blank"
                            rel="noopener noreferrer"
                            use:tooltip={"Open in new tab"}
                        >
                            {url}
                        </a>
                    </div>
                </div>
            </div>
        {/if}

        <Field class="form-field form-field-toggle" name="allowComments" let:uniqueId>
            <input type="checkbox" id={uniqueId} bind:checked={link.allowComments} />
            <label for={uniqueId}>Allow comments</label>
        </Field>

        <div class="col-12">
            <Field class="form-field form-field-toggle" name="passwordProtect" let:uniqueId>
                <input type="checkbox" id={uniqueId} bind:checked={link.passwordProtect} />
                <label for={uniqueId}>
                    <div class="txt">Protect with password</div>
                    {#if link.passwordProtect && initialPasswordProtect}
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <!-- svelte-ignore a11y-click-events-have-key-events -->
                        <span
                            class="inline-flex m-l-10 {!changePassword ? 'link-hint' : 'link-base'}"
                            on:click|preventDefault|stopPropagation={() => (changePassword = !changePassword)}
                        >
                            <span class="txt">Change password</span>
                            <i class="iconoir-nav-arrow-{changePassword ? 'up' : 'down'}" />
                        </span>
                    {/if}
                </label>
            </Field>
            {#if link.passwordProtect && changePassword}
                <div class="block" transition:slide={{ duration: 150 }}>
                    <Field class="form-field form-field-toggle p-t-xs" name="password" let:uniqueId>
                        <input
                            type="password"
                            id={uniqueId}
                            placeholder="Password"
                            minlength="5"
                            required
                            bind:value={password}
                        />
                    </Field>
                </div>
            {/if}
        </div>

        <div class="col-12">
            <Field class="form-field form-field-toggle" let:uniqueId>
                <input
                    type="checkbox"
                    id={uniqueId}
                    checked={link.onlyPrototypes?.length > 0}
                    disabled={!prototypesList.length}
                    on:change={(e) => {
                        restrictPrototypes = e.target.checked;
                    }}
                />
                <label for={uniqueId}>Restrict access to prototypes</label>
            </Field>
            {#if restrictPrototypes}
                <div class="block" transition:slide={{ duration: 150 }}>
                    <Field class="form-field link-prototypes-dropdown p-t-xs">
                        <ObjectSelect
                            items={prototypesList}
                            closable={false}
                            multiple
                            upside
                            selectPlaceholder="Select prototypes"
                            bind:keyOfSelected={link.onlyPrototypes}
                        />
                    </Field>
                </div>
            {/if}
        </div>
    </form>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent m-r-auto" on:click={hide}>
            <span class="txt">Back</span>
        </button>

        <button
            type="submit"
            form={formId}
            class="btn btn-expanded btn-primary"
            disabled={isSaving}
            class:btn-loading={isSaving}
        >
            <span class="txt">{link.id ? "Update" : "Create"} link</span>
        </button>
    </svelte:fragment>
</OverlayPanel>

<style>
    :global(.link-prototypes-dropdown .dropdown) {
        max-height: 150px;
    }
</style>
