<script>
    import { createEventDispatcher, onDestroy } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { confirm } from "@/stores/confirmation";
    import { addSuccessToast } from "@/stores/toasts";
    import { activeProject } from "@/stores/projects";
    import tooltip from "@/actions/tooltip";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import CopyIcon from "@/components/base/CopyIcon.svelte";
    import LinkUpsertPanel from "@/components/links/LinkUpsertPanel.svelte";
    import LinkSharePanel from "@/components/links/LinkSharePanel.svelte";

    const dispatch = createEventDispatcher();
    const linksRequestKey = "list_links";

    export let active = false;

    let panel;
    let sharePanel;
    let upsertPanel;
    let upsertPanelActive = false;
    let sharePanelActive = false;
    let links = [];
    let isLoading = false;
    let tempHide = false;

    $: tempHide = upsertPanelActive || sharePanelActive;

    export function show() {
        loadLinks();

        return panel?.show();
    }

    export function hide() {
        pb.cancelRequest(linksRequestKey);

        return panel?.hide();
    }

    async function loadLinks() {
        isLoading = true;

        try {
            links = await pb.collection("links").getFullList({
                filter: `project='${$activeProject?.id}'`,
                requestKey: linksRequestKey,
            });
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }

        isLoading = false;
    }

    function deleteLinkWithConfirm(link) {
        confirm("Do you really want to delete the selected project link?", () => deleteLink(link));
    }

    async function deleteLink(link) {
        if (!link?.id) {
            return;
        }

        try {
            await pb.collection("links").delete(link.id);

            utils.removeByKey(links, "id", link.id);

            links = links;

            addSuccessToast("Successfully deleted project link.");

            dispatch("delete", link);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }
    }

    onDestroy(() => {
        hide();
    });
</script>

<OverlayPanel bind:this={panel} bind:active popup class={tempHide ? "temp-hide" : ""} on:show on:hide>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">Project links</h5>
    </svelte:fragment>

    <div class="project-links-list">
        {#if isLoading}
            <div class="project-link m-t-10 m-b-10">
                <div class="skeleton-loader" />
            </div>
        {:else}
            {#each links as link (link.id)}
                {@const linkURL = utils.getProjectLinkURL(link)}
                <div class="project-link">
                    <div class="content">
                        <a
                            href={linkURL}
                            target="_blank"
                            rel="noopener noreferrer"
                            class="url"
                            use:tooltip={{ position: "top", text: "Open in new tab" }}
                        >
                            {linkURL}
                        </a>
                        <CopyIcon class="m-l-5" value={linkURL} />
                        <div class="meta">
                            <span
                                class="meta-item label-sm label label-{link.allowComments
                                    ? 'success'
                                    : 'disabled'}"
                            >
                                Comments
                            </span>
                            <span
                                class="meta-item label-sm label label-{link.passwordProtect
                                    ? 'success'
                                    : 'disabled'}"
                            >
                                Password
                            </span>
                            <span
                                class="meta-item label-sm label label-{link.onlyPrototypes?.length
                                    ? 'success'
                                    : 'disabled'}"
                            >
                                Restricted
                            </span>
                        </div>
                    </div>
                    <nav class="ctrls">
                        <button
                            type="button"
                            class="ctrl-item btn btn-sm btn-circle btn-transparent btn-hint"
                            use:tooltip={"Share"}
                            on:click={() => sharePanel?.show(link)}
                        >
                            <i class="iconoir-share-android" />
                        </button>
                        <button
                            type="button"
                            class="ctrl-item btn btn-sm btn-circle btn-transparent btn-hint"
                            use:tooltip={"Settings"}
                            on:click={() => upsertPanel?.show(link)}
                        >
                            <i class="iconoir-settings" />
                        </button>
                        <button
                            type="button"
                            class="ctrl-item btn btn-sm btn-circle btn-transparent btn-hint"
                            use:tooltip={"Delete"}
                            on:click={() => deleteLinkWithConfirm(link)}
                        >
                            <i class="iconoir-trash" />
                        </button>
                    </nav>
                </div>
            {:else}
                <div class="block txt-center txt-hint">
                    Create project links that you can send to your clients and teammates for feedback.
                </div>
            {/each}
        {/if}
    </div>

    <svelte:fragment slot="footer">
        <button type="button" class="btn btn-transparent m-r-auto" on:click={() => hide()}>
            <span class="txt">Close</span>
        </button>

        <button
            type="button"
            class="btn btn-expanded btn-primary"
            disabled={isLoading}
            on:click={upsertPanel?.show({ project: $activeProject.id, allowComments: true })}
        >
            <i class="iconoir-plus" />
            <span class="txt">New link</span>
        </button>
    </svelte:fragment>
</OverlayPanel>

<LinkUpsertPanel
    bind:this={upsertPanel}
    bind:active={upsertPanelActive}
    on:save={(e) => {
        utils.pushOrReplaceObject(links, e.detail);
        links = links;
    }}
/>

<LinkSharePanel bind:this={sharePanel} bind:active={sharePanelActive} />
