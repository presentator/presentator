<script>
    import { link } from "svelte-spa-router";
    import { fly } from "svelte/transition";
    import { flip } from "svelte/animate";
    import pb from "@/pb";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import { confirm } from "@/stores/confirmation";
    import { addSuccessToast } from "@/stores/toasts";
    import { prototypes, activePrototype } from "@/stores/prototypes";
    import {
        resetScreensStore,
        screens,
        isLoadingScreens,
        removeScreen,
        replaceScreenWithConfirm,
    } from "@/stores/screens";
    import { notifications } from "@/stores/notifications";
    import Field from "@/components/base/Field.svelte";
    import LazyImg from "@/components/base/LazyImg.svelte";
    import Toggler from "@/components/base/Toggler.svelte";
    import Droppable from "@/components/base/Droppable.svelte";
    import Sortable from "@/components/base/Sortable.svelte";
    import InlineTitleEdit from "@/components/base/InlineTitleEdit.svelte";
    import ScreenUploader from "@/components/screens/ScreenUploader.svelte";

    let oldActivePrototypeId;
    let selectedScreens = {};
    let isBulkDeleting = false;
    let isBulkMoving = false;
    let isReordering = false;
    let yieldMax = 0;

    $: if (oldActivePrototypeId != $activePrototype?.id) {
        reload();
    }

    $: totalSelected = Object.keys(selectedScreens).length;

    $: hasActiveBulkOperation = isBulkDeleting || isBulkMoving;

    async function reload() {
        oldActivePrototypeId = $activePrototype?.id;
        clearSelected();

        // break long rendering tasks into smaller ones
        yieldMax = 15;
        await loadScreens();
        while (yieldMax < $screens.length) {
            await utils.yieldToMain();
            yieldMax += 10;
        }
        yieldMax = 0;
    }

    async function loadScreens() {
        if (!$activePrototype?.id) {
            return;
        }

        resetScreensStore();

        $isLoadingScreens = true;

        try {
            let items = await pb.collection("screens").getFullList({
                filter: `prototype="${$activePrototype.id}"`,
                expand: 'comments(screen)',
                fields: '*,expand.comments(screen).id,expand.comments(screen).replyTo,expand.comments(screen).resolved',
                sort: "created",
            });

            // sort the items based on the prototype.screensOrder (if set)
            // ---
            const orderedItems = [];
            for (const id of $activePrototype.screensOrder) {
                const i = items.findIndex((item) => item.id == id);
                if (i < 0) {
                    continue;
                }

                orderedItems.push(items[i]);
                items.splice(i, 1);
            }

            // append any remaining screens
            for (let item of items) {
                orderedItems.push(item);
            }
            // ---

            $screens = orderedItems;

            $isLoadingScreens = false;
        } catch (err) {
            if (!err?.isAbort) {
                pb.error(err);
                $isLoadingScreens = false;
            }
        }
    }

    function keydownSelectAll(e) {
        if (e.ctrlKey && e.code == "KeyA" && !utils.isInput(e.target)) {
            e.preventDefault();

            for (const screen of $screens) {
                selectedScreens[screen.id] = true;
            }
        }
    }

    async function saveScreensOrder() {
        const ids = $screens.map((s) => s.id);

        if (JSON.stringify($activePrototype.screensOrder) == JSON.stringify(ids)) {
            return; // no change
        }

        try {
            isReordering = true;

            await pb.collection("prototypes").update($activePrototype.id, {
                screensOrder: ids,
            });

            $activePrototype.screensOrder = ids;

            isReordering = false;
        } catch (err) {
            if (!err.isAbort) {
                isReordering = false;
                pb.error(err);
            }
        }
    }

    function clearSelected() {
        selectedScreens = {};
    }

    async function moveSelectedWithConfirm(newPrototype) {
        const ids = Object.keys(selectedScreens);

        confirm(
            `Do you really want to move the selected ${ids.length} ${
                ids.length == 1 ? "screen" : "screens"
            }?`,
            async () => {
                return moveSelected(newPrototype);
            },
        );
    }

    async function moveSelected(newPrototype) {
        const ids = Object.keys(selectedScreens);

        if (!ids.length || !newPrototype?.id || hasActiveBulkOperation) {
            return;
        }

        isBulkMoving = true;

        try {
            const promises = [];

            // update the screens prototype field
            const data = { prototype: newPrototype.id };
            for (let id of ids) {
                promises.push(pb.collection("screens").update(id, data, { requestKey: "move_" + id }));
            }

            // remove the screens from the prototype preferred screensOrder
            promises.push(
                pb.collection("prototypes").update($activePrototype.id, {
                    "screensOrder-": ids,
                }),
            );

            await Promise.all(promises);

            clearSelected();

            $activePrototype = newPrototype.id;
        } catch (err) {
            pb.error(err);
        }

        isBulkMoving = false;
    }

    function deleteSelectedWithConfirm() {
        const ids = Object.keys(selectedScreens);

        confirm(
            `Do you really want to delete the selected ${ids.length} ${
                ids.length == 1 ? "screen" : "screens"
            }?`,
            async () => {
                return deleteSelected();
            },
        );
    }

    async function deleteSelected() {
        const ids = Object.keys(selectedScreens);

        if (!ids.length || hasActiveBulkOperation) {
            return;
        }

        isBulkDeleting = true;

        try {
            const promises = ids.map((id) => pb.collection("screens").delete(id));

            await Promise.all(promises);

            const deleted = $screens.filter((screen) => ids.includes(screen.id));
            for (const screen of deleted) {
                removeScreen(screen);
            }

            clearSelected();

            saveScreensOrder();

            addSuccessToast(`Successfully deleted ${ids.length} ${ids.length == 1 ? "screen" : "screens"}.`);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                reload();
            }
        }

        isBulkDeleting = false;
    }

    async function deleteScreenWithConfirm(screen) {
        confirm(
            `Do you really want to delete ${screen.title}?`,
            async () => {
                return deleteScreen(screen);
            },
            () => {
                document.activeElement?.blur();
            },
        );
    }

    async function deleteScreen(screen) {
        try {
            await pb.collection("screens").delete(screen.id);

            removeScreen(screen);

            clearSelected();

            addSuccessToast(`Successfully deleted screen ${screen.title}.`);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                reload();
            }
        }
    }

    function scrollToTheLast() {
        document.querySelector(".screens-list")?.scrollIntoView({
            behavior: "smooth",
            block: "end",
        });
    }
</script>

<svelte:window on:keydown={keydownSelectAll} />

{#if $isLoadingScreens && !$screens.length}
    <div class="block txt-center m-t-lg">
        <span class="loader loader-lg" />
    </div>
{:else if $activePrototype?.id}
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <div class="cards-list compact screens-list entrance-top" class:reordering={isReordering}>
        {#each yieldMax ? $screens.slice(0, yieldMax) : $screens as screen, i ($activePrototype?.id + screen.id)}
            {@const totalUnreadComments = $notifications?.filter(
                (n) => n.expand?.comment?.screen == screen.id,
            ).length}
            {@const totalUnreadAndUnresolvedPrimaryComments = $notifications?.filter(
                (n) => !n.expand?.comment?.replyTo && !n.expand?.comment?.resolved && n.expand?.comment?.screen == screen.id,
            ).length}
            {@const totalUnresolvedPrimaryComments = totalUnreadAndUnresolvedPrimaryComments + (screen.expand?.["comments(screen)"]?.filter((c) => !c.replyTo && !c.resolved)?.length || 0)}
            <div
                class="card screen-card"
                class:selected={selectedScreens[screen.id]}
                animate:flip={{ duration: 250 }}
            >
                <Droppable
                    let:dragover
                    on:drop={(e) => {
                        if (!isReordering) {
                            replaceScreenWithConfirm(screen, e.detail[0]);
                        }
                    }}
                >
                    <Sortable
                        bind:list={$screens}
                        index={i}
                        on:drag={(e) => {
                            // blank drag placeholder
                            if (!e.detail) {
                                return;
                            }

                            // append a dummy ghost element and then shortly
                            // remove it as a workaround for Safari/Gnome Web
                            const ghost = document.createElement("div");
                            ghost.className = "draggable-ghost";
                            document.body.appendChild(ghost);
                            e.detail.dataTransfer.setDragImage(ghost, 0, 0);
                            setTimeout(() => {
                                ghost?.remove();
                            }, 0);
                        }}
                        on:sort={() => {
                            saveScreensOrder();
                        }}
                    >
                        <figure class="card-featured" class:dragover draggable={false}>
                            {#if screen.isReplacing}
                                <span class="loader loader-lg" />
                            {:else if screen.file}
                                <div class="card-img" style:background-color={screen.background}>
                                    <LazyImg
                                        src={pb.files.getUrl(screen, screen.file, {
                                            thumb: "450x0",
                                        })}
                                        alt={screen.title}
                                        draggable={false}
                                    />
                                </div>
                            {:else}
                                <i class="iconoir-media-image" />
                            {/if}

                            <a
                                href={`/projects/${$activePrototype.project}/prototypes/${$activePrototype.id}/screens/${screen.id}`}
                                class="card-overlay handle"
                                use:link
                                draggable={false}
                            >
                                <div class="ctrl ctrl-center">
                                    <i class="iconoir-play-solid preview-icon" />
                                </div>
                                <div class="ctrl ctrl-top-left" on:click|stopPropagation>
                                    <Field class="form-field m-0" let:uniqueId>
                                        <input
                                            type="checkbox"
                                            id={uniqueId}
                                            bind:checked={selectedScreens[screen.id]}
                                            on:change={(e) => {
                                                if (!e.target.checked) {
                                                    delete selectedScreens[screen.id];
                                                    selectedScreens = selectedScreens;
                                                    e.target.blur();
                                                }
                                            }}
                                        />
                                        <label for={uniqueId} />
                                    </Field>
                                </div>
                                <div class="ctrl ctrl-top-right">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-circle btn-transparent"
                                        on:click|preventDefault|stopPropagation
                                    >
                                        <i class="iconoir-more-horiz" />
                                        <Toggler class="dropdown dropdown-sm">
                                            <div
                                                class="dropdown-item link-danger closable"
                                                on:click|preventDefault|stopPropagation={() =>
                                                    deleteScreenWithConfirm(screen)}
                                            >
                                                <i class="iconoir-trash" />
                                                <span class="txt">Delete</span>
                                            </div>
                                        </Toggler>
                                    </button>
                                </div>
                            </a>
                        </figure>
                    </Sortable>
                </Droppable>
                <div class="card-content">
                    <InlineTitleEdit tag="h5" class="title" collection="screens" bind:model={screen} />

                    <div class="meta">
                        {#if totalUnresolvedPrimaryComments}
                            <div
                                class="meta-item"
                                class:txt-warning={totalUnreadComments > 0}
                                use:tooltip={
                                    (totalUnresolvedPrimaryComments + " unresolved") +
                                    (totalUnreadComments ? ("\n" + totalUnreadComments + " unread") : "")
                                }
                            >
                                <i class="iconoir-message-text" />
                                <span class="txt">{totalUnresolvedPrimaryComments}</span>
                            </div>
                        {/if}

                        <div class="meta-item" use:tooltip={{
                            text: "Created " + utils.relativeDate(screen.created),
                            sub: utils.formatToLocalDate(screen.created),
                        }}>
                            <div class="iconoir-calendar" />
                        </div>
                    </div>
                </div>
            </div>
        {/each}

        <div class="card card-placeholder">
            <!-- note: using a manual id to allow triggering from external components -->
            <ScreenUploader id="screen_uploader" let:dragover let:isUploading on:upload={scrollToTheLast}>
                <label
                    for="screen_uploader"
                    class="card-featured"
                    class:dragover
                    class:no-pointer-events={isUploading}
                >
                    <div class="placeholder">
                        {#if isUploading}
                            <span class="loader loader-primary" />
                        {:else}
                            <div class="icon">
                                <i class="iconoir-upload-square" />
                            </div>
                            <div class="title">Upload screens</div>
                            <div class="content">Drop or click here to upload new screens</div>
                        {/if}
                    </div>
                </label>
            </ScreenUploader>
        </div>
    </div>

    {#if totalSelected}
        <div class="bulk-bar" transition:fly={{ duration: 150, y: 10, opacity: 0 }}>
            <div class="info">
                Selected <strong>{totalSelected} {totalSelected == 1 ? "screen" : "screens"}</strong>
            </div>

            {#if $prototypes.length > 1}
                <button
                    type="button"
                    class="btn btn-sm btn-semitransparent btn-expanded-sm"
                    class:btn-loading={isBulkMoving}
                    disabled={hasActiveBulkOperation}
                >
                    <span class="txt">Move to</span>
                    <i class="iconoir-nav-arrow-down" />
                    <Toggler class="dropdown dropdown-upside dropdown-sm">
                        {#each $prototypes as prototype (prototype.id)}
                            {#if prototype.id != $activePrototype?.id}
                                <button
                                    type="button"
                                    class="dropdown-item closable"
                                    on:click={() => moveSelectedWithConfirm(prototype)}
                                >
                                    <span class="txt">{prototype.title}</span>
                                </button>
                            {/if}
                        {/each}
                    </Toggler>
                </button>
            {/if}

            <button
                type="button"
                class="btn btn-sm btn-semitransparent btn-danger btn-expanded-sm"
                class:btn-loading={isBulkDeleting}
                disabled={hasActiveBulkOperation}
                on:click={deleteSelectedWithConfirm}
            >
                <span class="txt">Delete</span>
            </button>

            <button
                type="button"
                class="btn btn-sm btn-circle btn-transparent m-l-auto"
                disabled={hasActiveBulkOperation}
                use:tooltip={{ position: "top", text: "Clear selection" }}
                on:click={clearSelected}
            >
                <i class="iconoir-xmark" />
            </button>
        </div>
    {/if}
{/if}
