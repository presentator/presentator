<script>
    import pb from "@/pb";
    import { activeScale } from "@/stores/screens";
    import { selectedComment, addComment, removeUnsavedComments } from "@/stores/comments";
    import { notifications } from "@/stores/notifications";
    import Draggable from "@/components/base/Draggable.svelte";

    export let comment;
    export let disabled = false;
    export let scale = null; // option pin scale (fallback to $activeScale)

    const pinSize = 36;

    let isSaving = false;

    $: pinScale = scale === null ? $activeScale : scale;

    $: isUnread = comment?.id
        ? !!$notifications?.find((n) => {
              return !n.read && (n.comment == comment.id || n.expand?.comment?.replyTo == comment.id);
          })
        : false;

    async function savePosition(pin) {
        if (!pin) {
            return;
        }

        // merge in case the dom elem changes weren't reflected on the comment item
        Object.assign(comment, {
            left: pin.offsetLeft / pinScale,
            top: pin.offsetTop / pinScale,
        });

        // upsert store comment
        addComment(comment);

        if (!comment.id) {
            return; // new comment, not created yet
        }

        isSaving = true;

        try {
            const data = {
                left: comment.left,
                top: comment.top,
            };

            await pb.collection("comments").update(comment.id, data);

            isSaving = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSaving = false;
            }
        }
    }
</script>

<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<Draggable
    {disabled}
    class="comment-pin {comment.resolved ? 'resolved' : ''} {isUnread ? 'unread' : ''}"
    parentSelector=".screen-preview-img-wrapper"
    data-comment-pin={comment.id}
    style="left: min(calc(100% - {pinSize}px), {comment.left *
        pinScale}px); top: min(calc(100% - {pinSize}px), {comment.top * pinScale}px)"
    on:dragstop={(e) => {
        savePosition(e.detail.elem);
    }}
    on:click={() => {
        if ($selectedComment != comment) {
            removeUnsavedComments();
        }
        $selectedComment = comment;
    }}
>
    {#if isSaving}
        <span class="loader loader-xs loader-danger-alt" />
    {:else}
        <slot />
    {/if}
</Draggable>
