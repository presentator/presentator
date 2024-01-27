<script>
    import { createEventDispatcher, tick } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { loggedUser } from "@/stores/app";
    import { setErrors } from "@/stores/errors";
    import { activeProject } from "@/stores/projects";
    import { activeScreen } from "@/stores/screens";
    import {
        activeScreenComments,
        selectedComment,
        comments,
        addComment,
        isCommentDeleting,
        removeUnsavedComments,
    } from "@/stores/comments";
    import { notifications, removeNotification } from "@/stores/notifications";
    import tooltip from "@/actions/tooltip";
    import Popover from "@/components/base/Popover.svelte";
    import Toggler from "@/components/base/Toggler.svelte";
    import Field from "@/components/base/Field.svelte";
    import CommentListItem from "@/components/comments/CommentListItem.svelte";
    import CommentCollaborators from "@/components/comments/CommentCollaborators.svelte";

    const dispatch = createEventDispatcher();

    const emojis = {
        "+1": "👍",
        "-1": "👎",
        ok: "👌",
        pray: "🙏",
        smile: "🙂",
        confused: "😕",
        thinking: "🤔",
        joy: "😂",
        hooray: "🎉",
        watching: "👀",
        love: "❤️",
        poop: "💩",
    };

    const GUEST_EMAIL_KEY = "guestEmail";

    export let viewport = null;

    let popover;
    let messageInput;
    let message = "";
    let guestEmail = "";
    let commentsList = [];
    let isSaving = false;
    let collaborators = []; // array list of options in the format [{label, value}]

    $: primaryComment =
        $activeScreenComments.find((c) => c.id && c.id == $selectedComment?.replyTo) || $selectedComment;

    $: replies = primaryComment?.id
        ? $activeScreenComments.filter((c) => c.replyTo === primaryComment.id)
        : [];

    $: commentsList = primaryComment?.id ? [primaryComment].concat(replies) : [];

    $: canCreate = !isSaving && message != "";

    $: canHide = !isSaving && !$isCommentDeleting;

    $: if ($activeScreen) {
        onSelectedCommentChange($selectedComment);
    }

    $: isProjectOwner = $loggedUser?.id && $activeProject?.expand?.users?.find((u) => u.id == $loggedUser.id);

    $: needGuestEmail = !isProjectOwner && !$loggedUser?.email;

    // cache the guestEmail
    $: if (guestEmail) {
        window.localStorage.setItem(GUEST_EMAIL_KEY, guestEmail);
    }

    async function onSelectedCommentChange(comment) {
        if (!comment) {
            popover?.forceHide();
            return;
        }

        await tick(); // ensures that the dom has been updated

        const pinId = comment.replyTo || comment.id;
        const commentPin = document.querySelector(`[data-comment-pin="${pinId}"]`);
        if (!commentPin) {
            popover?.forceHide();
            return;
        }

        resetForm();

        commentPin.scrollIntoView({
            block: "nearest",
            inline: "nearest",
        });

        refreshCollaboratorsList();

        popover?.show(commentPin, viewport);

        markAllAsRead();
    }

    function onHide() {
        removeUnsavedComments();

        $selectedComment = null;
    }

    function resetForm(clearMessage = false) {
        setErrors({});
        guestEmail = guestEmail || window.localStorage.getItem(GUEST_EMAIL_KEY) || "";
        if (clearMessage) {
            message = "";
        } else {
            message = ("" + message).trim();
        }
        isSaving = false;
    }

    async function create() {
        if (!canCreate) {
            return;
        }

        isSaving = true;

        const data = {
            screen: primaryComment?.screen,
            left: primaryComment?.left,
            top: primaryComment?.top,
            replyTo: primaryComment?.id,
            message: ("" + message).trim(),
        };

        if (isProjectOwner) {
            data.user = $loggedUser.id;
        } else if ($loggedUser.email) {
            data.guestEmail = $loggedUser.email;
        } else {
            data.guestEmail = guestEmail;
        }

        try {
            const comment = await pb.collection("comments").create(data, {
                expand: "user",
            });

            resetForm(true);

            await tick();

            removeUnsavedComments();

            addComment(comment, true);

            dispatch("create", comment);
        } catch (err) {
            if (err.isAbort) {
                return;
            }

            // manually add custom error indicating that the guestEmail is invalid
            if (data.guestEmail && utils.isEmpty(err.response.data)) {
                err.response.data = {
                    guestEmail: {
                        code: "invalid_guestEmail",
                        message: "The guest email is invalid or it matches with one of project owners.",
                    },
                };
            }

            pb.error(err);

            isSaving = false;
        }
    }

    async function markAsResolved(state = true) {
        if (isSaving && !primaryComment) {
            return;
        }

        isSaving = true;

        try {
            const comment = await pb.collection("comments").update(primaryComment.id, {
                resolved: state,
            });

            isSaving = false;

            await tick();

            if (state) {
                popover?.forceHide();
            }

            addComment(comment);

            dispatch("resolved", comment);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSaving = false;
            }
        }
    }

    // mark the primary comment and all its replies as "read".
    async function markAllAsRead() {
        if (!isProjectOwner) {
            return;
        }

        const commentIds = commentsList.map((c) => c.id);

        const unreadList = $notifications.filter((n) => commentIds.includes(n.comment));

        const promises = [];
        for (let notification of unreadList) {
            // optimistic update
            removeNotification(notification);

            promises.push(pb.collection("notifications").update(notification.id, { read: true }));
        }

        return Promise.allSettled(promises);
    }

    function refreshCollaboratorsList() {
        collaborators = [];

        const guestEmails = [];

        // extract the users from the project admins and comments lists
        const users = ($activeProject?.expand?.users || []).slice();
        for (const comment of $comments) {
            if (comment.expand?.user) {
                utils.pushOrReplaceObject(users, comment.expand?.user);
            } else if (comment.guestEmail) {
                utils.pushUnique(guestEmails, comment.guestEmail);
            }
        }

        // users
        for (const u of users) {
            if (u.id == $loggedUser?.id) {
                continue;
            }

            const displayValue = u.name || u.email;
            collaborators.push({
                label: u.username + (displayValue ? ` (${displayValue})` : ""),
                value: u.username,
            });
        }

        // guests
        for (const email of guestEmails) {
            if (email == guestEmail || email == $loggedUser?.email) {
                continue;
            }

            collaborators.push({
                label: email,
                value: email,
            });
        }
    }
</script>

<Popover
    bind:this={popover}
    class="comments-popover"
    disableHide={!canHide}
    escHide={canHide}
    vPadding={0}
    hPadding={5}
    on:hide={onHide}
    {...$$restProps}
>
    {#if primaryComment?.id}
        <Field class="form-field form-field-toggle form-field-sm resolved-checkbox" let:uniqueId>
            <input
                type="checkbox"
                id={uniqueId}
                checked={primaryComment?.resolved}
                on:change={(e) => {
                    markAsResolved(e.target.checked);
                }}
            />
            <label for={uniqueId}>Mark as resolved</label>
        </Field>
    {/if}

    {#if commentsList.length}
        <div class="list comments-list">
            {#each commentsList as comment}
                <CommentListItem readonly={!isProjectOwner} {comment} />
            {/each}
        </div>
    {/if}

    <!-- svelte-ignore a11y-autofocus -->
    <div class="comment-form">
        {#if needGuestEmail}
            <Field class="form-field" name="guestEmail" inlineError let:uniqueId>
                <div class="field-group">
                    <label class="addon" for={uniqueId}>
                        <i class="iconoir-mail" />
                    </label>

                    <input
                        type="email"
                        id={uniqueId}
                        placeholder="Your email"
                        autofocus={!guestEmail && !primaryComment?.id ? true : null}
                        bind:value={guestEmail}
                    />
                </div>
            </Field>
        {/if}

        <Field class="form-field" name="message" inlineError let:uniqueId>
            <textarea
                bind:this={messageInput}
                id={uniqueId}
                autofocus={!needGuestEmail && !primaryComment?.id ? true : null}
                placeholder="Write a comment (@ to mention)"
                bind:value={message}
                on:keydown={(e) => {
                    if (e.ctrlKey && e.code === "Enter") {
                        e.preventDefault();
                        create();
                    }
                }}
            />
            <div class="ctrls">
                <button
                    type="button"
                    class="btn btn-transparent btn-hint btn-sm btn-circle"
                    disabled={isSaving}
                    use:tooltip={{ position: "right", text: "Emojis" }}
                >
                    <i class="iconoir-emoji" />
                    <Toggler class="dropdown emojis-dropdown">
                        {#each Object.entries(emojis) as [title, emoji]}
                            <button
                                type="button"
                                {title}
                                class="emoji closable"
                                on:click|preventDefault={() => {
                                    message += emoji;
                                }}
                            >
                                {emoji}
                            </button>
                        {/each}
                    </Toggler>
                </button>
                <button
                    type="button"
                    class="btn btn-transparent btn-hint btn-sm btn-circle"
                    class:btn-loading={isSaving}
                    use:tooltip={{ position: "right", text: "Send (Ctrl + Enter)" }}
                    on:click|preventDefault={create}
                >
                    <i class="iconoir-send" />
                </button>
            </div>
        </Field>

        <CommentCollaborators
            input={messageInput}
            list={collaborators}
            on:hide={async () => {
                await tick();
                popover?.refreshPosition();
            }}
            on:show={async () => {
                await tick();
                popover?.refreshPosition();
            }}
        />
    </div>
</Popover>
