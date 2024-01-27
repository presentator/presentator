<script>
    import { createEventDispatcher, tick } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { confirm } from "@/stores/confirmation";
    import { selectedComment, removeComment, isCommentDeleting } from "@/stores/comments";
    import Toggler from "@/components/base/Toggler.svelte";
    import UserThumb from "@/components/users/UserThumb.svelte";

    const dispatch = createEventDispatcher();

    export let comment;
    export let readonly = false;

    let listItem;

    // ensures that the comment is within the visible scrollarea
    $: if (listItem && $selectedComment?.id == comment.id) {
        scrollIntoView();
    }

    async function scrollIntoView() {
        await tick();

        listItem?.scrollIntoView({
            block: "nearest",
            inline: "nearest",
        });
    }

    function deleteWithConfirm() {
        if (!comment?.id || readonly) {
            return;
        }

        let message = "Do you really want to delete the selected comment?";
        if (!comment.replyTo) {
            message = "Do you really want to delete the selected comment and all of its replies?";
        }

        $isCommentDeleting = true;

        confirm(
            message,
            async () => {
                return deleteComment();
            },
            () => {
                // reorder exec queue
                setTimeout(() => {
                    $isCommentDeleting = false;
                }, 0);
            }
        );
    }

    async function deleteComment() {
        if (!comment?.id || readonly) {
            return;
        }

        $isCommentDeleting = true;

        try {
            await pb.collection("comments").delete(comment.id);

            dispatch("delete", comment);

            removeComment(comment);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }

        $isCommentDeleting = false;
    }
</script>

<div bind:this={listItem} class="list-item {comment.replyTo ? 'reply' : 'primary'}" class:unread={false}>
    <UserThumb user={comment.expand?.user} class="avatar" />
    <div class="content">
        <div class="row">
            <div class="inline-flex flex-row-gap-0">
                <small class="name">{utils.getCommentAuthor(comment)}</small>
                <small class="txt-xs txt-hint date">
                    {utils.relativeDate(comment.created)}
                </small>
            </div>
            {#if !readonly}
                <button
                    type="button"
                    class="btn btn-transparent btn-hint btn-xs btn-options"
                    aria-label="Comment options"
                >
                    <i class="iconoir-more-horiz" />
                    <Toggler class="dropdown dropdown-sm comment-dropdown">
                        <!-- svelte-ignore a11y-click-events-have-key-events -->
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div class="dropdown-item link-danger" on:click={() => deleteWithConfirm(comment)}>
                            <i class="iconoir-trash" />
                            <span class="txt">Delete</span>
                        </div>
                    </Toggler>
                </button>
            {/if}
        </div>

        <div class="row">
            <div class="message">{comment.message}</div>
        </div>

        <slot />
    </div>
</div>
