<script>
    import utils from "@/utils";
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import {
        resetPrototypesStore,
        prototypes,
        activePrototype,
        isLoadingPrototypes,
        addPrototype,
        removePrototype,
    } from "@/stores/prototypes";
    import { confirm } from "@/stores/confirmation";
    import { addSuccessToast } from "@/stores/toasts";
    import Toggler from "@/components/base/Toggler.svelte";
    import PrototypeUpsertPanel from "@/components/prototypes/PrototypeUpsertPanel.svelte";

    export let projectId;
    export let initialPrototypeId = "";

    let upsertPanel;

    $: load(projectId, initialPrototypeId);

    async function load(projectIdArg, prototypeIdArg) {
        if (!projectIdArg) {
            return;
        }

        $isLoadingPrototypes = true;

        try {
            $activePrototype = prototypeIdArg;

            $prototypes = await pb.collection("prototypes").getFullList({
                filter: `project="${projectIdArg}"`,
                sort: "created",
            });

            $isLoadingPrototypes = false;
        } catch (err) {
            if (!err?.isAbort) {
                pb.error(err);
                $isLoadingPrototypes = false;
            }
        }
    }

    function deleteConfirm(prototype) {
        if (!prototype?.id) {
            return;
        }

        confirm(`Do you really want to delete the selected prototype and all its screens?`, () => {
            return pb
                .collection("prototypes")
                .delete(prototype.id)
                .then(() => {
                    removePrototype(prototype);
                    addSuccessToast(`Successfully deleted prototype ${prototype.title}.`);
                })
                .catch((err) => {
                    pb.error(err);
                });
        });
    }

    function duplicateWithConfirm(prototype) {
        confirm(
            `Do you really want to duplicate the selected prototype and all of its screens and hotspots?`,
            async () => duplicate(prototype)
        );
    }

    async function duplicate(prototype) {
        if (!prototype?.id) {
            return;
        }

        try {
            const duplicated = await pb.send("/api/pr/duplicate-prototype/" + encodeURIComponent(prototype.id), {
                method: "POST",
            });

            addPrototype(duplicated, true);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    function newPrototype() {
        upsertPanel?.show({
            project: projectId,
        });
    }
</script>

{#if $isLoadingPrototypes}
    <div class="block txt-center m-t-lg">
        <span class="loader loader-lg" />
    </div>
{:else if !$isLoadingPrototypes && !$prototypes.length}
    <div class="placeholder-block entrance-top">
        <div class="icon">
            <i class="iconoir-pen-tablet" />
        </div>
        <h6 class="title">The project doesn't have any prototypes yet!</h6>
        <div class="content">
            <p>Create a new desktop or mobile prototype.</p>
        </div>
        <button type="button" class="btn btn-expanded btn-primary btn-lg m-t-base" on:click={newPrototype}>
            <i class="iconoir-plus" />
            <span class="txt">New prototype</span>
        </button>
    </div>
{:else}
    <nav class="prototypes-bar entrance-left">
        <slot name="before" />

        {#each $prototypes as prototype (prototype.id)}
            <button
                type="button"
                class="btn {$activePrototype?.id == prototype.id
                    ? 'btn-semitransparent'
                    : 'btn-transparent link-hint'}"
                on:click|preventDefault={() => {
                    $activePrototype = prototype.id;
                }}
            >
                <i class={utils.getPrototypeIcon(prototype)} />

                <span class="txt txt-ellipsis">{prototype.title}</span>

                <div class="meta">
                    <div class="meta-item">
                        <i class="iconoir-more-vert txt-sm txt-sm" />
                        <Toggler class="dropdown dropdown-nowrap dropdown-sm dropdown-right">
                            <button
                                type="button"
                                class="dropdown-item closable"
                                on:click|preventDefault={() => upsertPanel?.show(prototype)}
                            >
                                <i class="iconoir-edit-pencil" />
                                <span class="txt">Edit</span>
                            </button>
                            <button
                                type="button"
                                class="dropdown-item closable"
                                on:click|preventDefault={() => duplicateWithConfirm(prototype)}
                            >
                                <i class="iconoir-copy" />
                                <span class="txt">Duplicate</span>
                            </button>
                            <hr />
                            <button
                                type="button"
                                class="dropdown-item link-danger closable"
                                on:click|preventDefault={() => deleteConfirm(prototype)}
                            >
                                <i class="iconoir-trash" />
                                <span class="txt">Delete</span>
                            </button>
                        </Toggler>
                    </div>
                </div>
            </button>
        {/each}

        <button
            type="button"
            class="btn btn-circle btn-transparent link-hint"
            use:tooltip={{ text: "New prototype", position: "right" }}
            on:click={newPrototype}
        >
            <i class="iconoir-plus" />
        </button>

        <slot name="after" />
    </nav>
{/if}

<PrototypeUpsertPanel
    bind:this={upsertPanel}
    on:submit={(e) => {
        if (e.detail) {
            addPrototype(e.detail, true);
        }
    }}
/>

<style>
    .prototypes-bar {
        position: relative;
        z-index: 9;
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        width: 100%;
        margin: 0 0 var(--baseSpacing);
    }
</style>
