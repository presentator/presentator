<script>
    import { slide } from "svelte/transition";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import { activeScreenPrimaryComments, selectedComment, showResolved } from "@/stores/comments";
    import Field from "@/components/base/Field.svelte";

    export let active = false;

    $: primaryComments = $activeScreenPrimaryComments?.filter((c) => !!c.id);

    $: totalResolved = primaryComments.filter((c) => c.resolved).length;

    $: filteredComments = $showResolved ? primaryComments : primaryComments.filter((c) => !c.resolved);

    $: if (totalResolved == 0) {
        $showResolved = false;
    }

    export function toggle() {
        if (active) {
            hide();
        } else {
            show();
        }
    }

    export function show() {
        active = true;
    }

    export function hide() {
        active = false;
    }

    function onSlideEnd() {
        utils.triggerEvent("resize", { noDebounce: true });
    }
</script>

{#if active}
    <aside
        transition:slide={{ duration: 200, axis: "x" }}
        class="screen-preview-sidebar comments-sidebar"
        on:introend={onSlideEnd}
        on:outroend={onSlideEnd}
    >
        <header class="sidebar-section sidebar-header">
            <h6>Screen comments</h6>
            <button
                type="button"
                class="btn btn-sm btn-transparent btn-hint btn-circle close-btn"
                use:tooltip={{ position: "left", text: "Close" }}
                on:click|preventDefault={hide}
            >
                <i class="iconoir-xmark" />
            </button>

            {#if primaryComments.length}
                <Field class="form-field form-field-toggle m-t-xs m-b-5 entrance-top" let:uniqueId>
                    <input
                        type="checkbox"
                        id={uniqueId}
                        disabled={totalResolved == 0}
                        bind:checked={$showResolved}
                    />
                    <label for={uniqueId}>Show resolved comments ({totalResolved})</label>
                </Field>
            {/if}
        </header>
        <div class="sidebar-section sidebar-content">
            {#if !filteredComments.length}
                <div class="placeholder-block entrance-top">
                    <div class="icon">
                        <i class="iconoir-message-alert" />
                    </div>
                    <div class="title txt-disabled">
                        {#if !$showResolved && totalResolved > 0}
                            All comments have been resolved.
                        {:else}
                            No comments found.
                        {/if}
                    </div>
                </div>
            {:else}
                <div class="flex-list primary-comments-list entrance-top">
                    {#each primaryComments as comment, i (comment.id)}
                        {#if $showResolved || !comment.resolved}
                            {@const totalReplies =
                                primaryComments.filter((c) => c.replyTo == comment.id).length || 0}
                            <button
                                type="button"
                                class="bubble primary-comment"
                                class:active={comment.id == $selectedComment?.id}
                                on:click|preventDefault={() => {
                                    $selectedComment = comment;
                                }}
                            >
                                <div class="meta">
                                    <strong class="meta-item number">#{i + 1}</strong>
                                    <span class="meta-item txt-ellipsis">
                                        {utils.getCommentAuthor(comment)}
                                    </span>
                                    {#if comment.resolved}
                                        <span class="meta-item resolved m-l-auto" use:tooltip={"Resolved"}>
                                            <i class="iconoir-double-check txt-success" />
                                        </span>
                                    {/if}
                                </div>
                                <div class="content">
                                    {comment.message}
                                </div>
                                <div class="meta">
                                    <span class="meta-item date">
                                        {utils.relativeDate(comment.created)}
                                    </span>
                                    {#if totalReplies > 0}
                                        <span class="meta-item m-l-auto">
                                            {totalReplies}
                                            {totalReplies == 1 ? "reply" : "replies"}
                                        </span>
                                    {/if}
                                </div>
                            </button>
                        {/if}
                    {/each}
                </div>
            {/if}
        </div>
    </aside>
{/if}
