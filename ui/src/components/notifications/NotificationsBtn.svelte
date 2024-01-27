<script>
    import { onMount } from "svelte";
    import { push } from "svelte-spa-router";
    import tooltip from "@/actions/tooltip";
    import pb from "@/pb";
    import utils from "@/utils";
    import { notifications, removeNotification } from "@/stores/notifications";
    import Toggler from "@/components/base/Toggler.svelte";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import UserThumb from "@/components/users/UserThumb.svelte";

    let panel;
    let togglerActive = false;
    let isSubmitting = false;
    let projectFilter = "";
    let projectOptions = [];
    let filteredNotifications = [];

    $: if ($notifications?.length) {
        refreshProjectOptions();
    }

    $: filteredNotifications = !projectFilter
        ? $notifications
        : fallbackArray(
              $notifications.filter((n) => {
                  return n.expand?.comment?.expand?.screen?.expand?.["prototype"]?.project == projectFilter;
              }),
              $notifications
          );

    export function show() {
        panel?.show();
    }

    export function hide() {
        panel?.hide();
    }

    function onShow() {
        projectFilter = "";
        refreshProjectOptions();
    }

    function fallbackArray(arr, fallback = []) {
        if (arr.length) {
            return arr;
        }

        return fallback;
    }

    function refreshProjectOptions() {
        const items = {};

        for (let notification of $notifications) {
            const project =
                notification.expand?.comment?.expand?.screen?.expand?.["prototype"]?.expand?.project;
            if (project) {
                items[project.id] = {
                    label: project.title,
                    value: project.id,
                };
            }
        }

        projectOptions = Object.values(items);
        if (projectOptions.length != 1) {
            projectOptions = [
                {
                    label: "All projects",
                    value: "",
                },
            ].concat(projectOptions);
        } else {
            projectFilter = projectOptions[0].value;
        }
    }

    // Marks the specified notifications as read.
    //
    // If `ids` is empty, marks all notifications.
    async function markAsRead(ids) {
        isSubmitting = true;

        ids = utils.toArray(ids);
        if (!ids.length) {
            ids = $notifications.map((n) => n.id);
        }

        try {
            const promises = [];

            for (let id of ids) {
                promises.push(
                    pb.collection("notifications").update(id, {
                        read: true,
                    })
                );

                // optimistic update
                removeNotification(id);
            }

            await Promise.allSettled(promises);
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
            }
        }

        isSubmitting = false;
    }

    function goToComment(comment) {
        const projectId = comment.expand?.screen?.expand?.["prototype"]?.project;
        const prototypeId = comment.expand?.screen?.["prototype"];
        const screenId = comment.screen;
        const commentId = comment.id;

        push(
            `/projects/${projectId}/prototypes/${prototypeId}/screens/${screenId}?mode=comments&commentId=${commentId}`
        );
    }

    onMount(() => {
        if (utils.getHashQueryParams().notifications) {
            utils.replaceHashQueryParams({ notifications: null });
            show();
        }
    });
</script>

<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<div
    tabindex="0"
    class="btn btn-hint btn-transparent btn-circle notifications-btn"
    use:tooltip={!togglerActive ? { position: "bottom", text: "Notifications" } : undefined}
>
    <i class="iconoir-bell {$notifications.length ? 'txt-warning' : 'txt-hint'}" />

    {#if $notifications.length}
        <sub class="sub sub-warning" title="Unread notifications">
            {$notifications.length}
        </sub>
    {/if}

    <Toggler
        bind:this={panel}
        bind:active={togglerActive}
        class="dropdown notifications-dropdown"
        on:show={onShow}
    >
        <div class="content">
            {#if $notifications.length}
                <div class="block p-t-xs p-b-sm p-l-sm p-r-sm">
                    <div class="flex m-b-xs">
                        <h6>Notifications</h6>
                    </div>
                    <div class="form-field form-field-sm m-b-0 m-l-auto">
                        <div class="field-group">
                            <ObjectSelect items={projectOptions} bind:keyOfSelected={projectFilter} />
                            <div class="addon">
                                <button
                                    type="button"
                                    class="txt-sm link-fade"
                                    class:txt-disabled={isSubmitting}
                                    on:click|preventDefault={() => markAsRead()}
                                >
                                    <span class="txt">Mark all as read</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list notifications-list">
                    {#each filteredNotifications as notification (notification.id)}
                        {@const comment = notification.expand?.comment || {}}

                        <!-- svelte-ignore a11y-click-events-have-key-events -->
                        <!-- svelte-ignore a11y-no-static-element-interactions -->
                        <div class="list-item handle" on:click={() => goToComment(comment)}>
                            <UserThumb user={comment.expand?.user} class="avatar" />

                            <div class="content">
                                <div class="row">
                                    <div class="inline-flex flex-row-gap-0">
                                        <small class="name">{utils.getCommentAuthor(comment)}</small>
                                        <small class="txt-xs txt-hint date">
                                            {utils.relativeDate(comment.created)}
                                        </small>
                                    </div>
                                    <nav class="ctrls">
                                        <button
                                            type="button"
                                            class="btn btn-xs btn-circle btn-transparent btn-hint"
                                            use:tooltip={"Mark as read"}
                                            on:click|preventDefault|stopPropagation={() => {
                                                markAsRead(notification.id);
                                            }}
                                        >
                                            <i class="iconoir-check" />
                                        </button>
                                    </nav>
                                </div>

                                <div class="row">
                                    <div class="message">{comment.message}</div>
                                </div>

                                {#if comment.expand?.screen?.title}
                                    <div class="row txt-hint">
                                        <small class="screen-title">{comment.expand.screen.title}</small>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    {/each}
                </div>
            {:else}
                <div class="placeholder-block placeholder-block-sm p-base">
                    <figure class="icon">
                        <i class="iconoir-bell" />
                    </figure>
                    <p class="txt-hint">You don't have any unread notifications!</p>
                </div>
            {/if}
        </div>
    </Toggler>
</div>
