<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import { confirm } from "@/stores/confirmation";
    import { removeComment } from "@/stores/comments";
    import Toggler from "@/components/base/Toggler.svelte";

    const dispatch = createEventDispatcher();

    export let comment;
    export let isDeleting = false;

    function deleteWithConfirm() {
        if (!comment?.id) {
            return;
        }

        let message = "Do you really want to delete the selected comment?";
        if (!comment.replyTo) {
            message = "Do you really want to delete the selected comment and all of its replies?";
        }

        isDeleting = true;

        confirm(
            message,
            async () => {
                return deleteComment(comment);
            },
            () => {
                // reorder exec queue
                setTimeout(() => {
                    isDeleting = false;
                }, 0);
            }
        );
    }

    async function deleteComment() {
        if (!comment?.id) {
            return;
        }

        isDeleting = true;

        try {
            await pb.collection("comments").delete(comment.id);

            removeComment(comment);

            isDeleting = false;

            dispatch("delete", comment);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isDeleting = false;
            }
        }
    }
</script>

<button type="button" class="btn btn-transparent btn-hint btn-xs btn-options" aria-label="Comment options">
    <i class="iconoir-more-horiz" />
    <Toggler class="dropdown dropdown-sm">
        <!-- svelte-ignore a11y-click-events-have-key-events -->
        <!-- svelte-ignore a11y-no-static-element-interactions -->
        <div class="dropdown-item link-danger" on:click|preventDefault={() => deleteWithConfirm(comment)}>
            <i class="iconoir-trash" />
            <span class="txt">Delete</span>
        </div>
    </Toggler>
</button>
