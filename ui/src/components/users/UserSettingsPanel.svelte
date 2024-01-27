<script>
    import { createEventDispatcher, tick } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { confirm } from "@/stores/confirmation";
    import { loggedUser } from "@/stores/app";
    import { setErrors } from "@/stores/errors";
    import { addSuccessToast } from "@/stores/toasts";
    import tooltip from "@/actions/tooltip";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import Toggler from "@/components/base/Toggler.svelte";
    import Field from "@/components/base/Field.svelte";
    import UserThumb from "@/components/users/UserThumb.svelte";
    import ChangeEmailPanel from "@/components/users/ChangeEmailPanel.svelte";

    const dispatch = createEventDispatcher();
    const formId = "settings_" + utils.randomString(5);

    let panel;
    let changeEmailPanel;
    let user = {};
    let files = null;
    let isSaving = false;
    let isDeleting = false;
    let isSendingPasswordReset = false;
    let initialUserHash = "";

    $: hasAvatar = user?.avatar || files?.length;

    $: hasChanges = files?.length || initialUserHash != JSON.stringify(user);

    $: canHide = !isSaving && !isDeleting && !isSendingPasswordReset;

    export function show(model) {
        reset();

        user = structuredClone(model || {});

        initialUserHash = JSON.stringify(user);

        return panel?.show();
    }

    export function hide() {
        return panel?.hide();
    }

    function reset() {
        setErrors({});

        files = null;
    }

    function clearAvatar() {
        user.avatar = null;
        files = null;
    }

    async function save() {
        isSaving = true;

        try {
            const data = {
                name: user.name,
                username: user.username,
                emailVisibility: user.emailVisibility,
                allowEmailNotifications: user.allowEmailNotifications,
                avatar: user.avatar,
            };

            if (files?.length) {
                data.avatar = files[0];
            }

            user = await pb.collection("users").update(user.id, data);

            isSaving = false;

            dispatch("save", user);

            addSuccessToast("Successfully saved account settings.");

            await tick(); // wait the reactive fields to update

            hide();
        } catch (err) {
            if (!err.isAbort) {
                isSaving = false;
                pb.error(err);
            }
        }
    }

    function deleteWithConfirm() {
        if (!user?.id) {
            return;
        }

        let message =
            "Do you really want to delete your Presentator account and all of its related resources?\n\nNote that projects that have more than one owner will not be deleted.";

        isDeleting = true;

        confirm(
            message,
            async () => {
                return deleteUser();
            },
            () => {
                // reorder exec queue
                setTimeout(() => {
                    isDeleting = false;
                }, 0);
            }
        );
    }

    async function deleteUser() {
        if (!user?.id) {
            return;
        }

        isDeleting = true;

        try {
            const isLoggedUser = user.id == $loggedUser?.id;

            await pb.collection("users").delete(user.id);

            isDeleting = false;

            dispatch("delete", user);

            addSuccessToast(`Successfully deleted user ${user.username}.`);

            if (isLoggedUser) {
                pb.logout();
            }
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isDeleting = false;
            }
        }
    }

    async function requestPasswordReset() {
        if (!user?.email) {
            return;
        }

        isSendingPasswordReset = true;

        try {
            await pb.collection("users").requestPasswordReset(user.email);

            addSuccessToast(`Successfully sent password change email to ${user.email}.`);

            isSendingPasswordReset = false;
        } catch (err) {
            if (!err.isAbort) {
                pb.error(err);
                isSendingPasswordReset = false;
            }
        }
    }
</script>

<!-- svelte-ignore a11y-no-static-element-interactions -->
<!-- svelte-ignore a11y-click-events-have-key-events -->
<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<OverlayPanel bind:this={panel} beforeHide={() => canHide} popup on:show on:hide>
    <svelte:fragment slot="header">
        <h5 class="block txt-center">Account settings</h5>
    </svelte:fragment>

    <form id={formId} class="grid grid-sm" on:submit|preventDefault={save}>
        <Field class="avatar-field block txt-center" name="avatar" let:uniqueId>
            <input type="file" class="hidden" bind:files id={uniqueId} />
            <svelte:element
                this={hasAvatar ? "div" : "label"}
                for={!hasAvatar ? uniqueId : null}
                class="inline-flex"
                use:tooltip={!hasAvatar ? "Upload avatar" : null}>
                <UserThumb
                    class="thumb-xl {!hasAvatar ? 'thumb-handle' : ''}"
                    {user}
                    file={files?.[0]}
                    fallbackToOAuth2={false}>
                    <svelte:fragment slot="placeholder">
                        <i class="iconoir-media-image-plus" />
                    </svelte:fragment>

                    {#if hasAvatar}
                        <button
                            type="button"
                            class="btn btn-xs btn-outline btn-circle btn-close"
                            use:tooltip={{ position: "right", text: "Remove avatar" }}
                            on:click|preventDefault|stopPropagation={clearAvatar}>
                            <i class="iconoir-xmark txt-sm" />
                        </button>
                    {/if}
                </UserThumb>
            </svelte:element>
        </Field>

        <Field class="form-field" name="name" let:uniqueId>
            <label for={uniqueId}>Full name</label>
            <input type="text" id={uniqueId} bind:value={user.name} />
        </Field>

        <Field class="form-field required" name="username" let:uniqueId>
            <label for={uniqueId}>Username</label>
            <input type="text" id={uniqueId} required bind:value={user.username} />
        </Field>

        <Field class="form-field" name="email" let:uniqueId>
            <label for={uniqueId}>Email</label>
            <div class="field-group">
                <input type="email" id={uniqueId} value={user.email} readonly />
                {#if $loggedUser?.id == user.id}
                    <button
                        type="button"
                        class="btn btn-xs btn-transparent"
                        on:click|stopPropagation={changeEmailPanel?.show()}>
                        {#if user.email}
                            Change email
                        {:else}
                            Set email
                        {/if}
                    </button>
                {/if}
            </div>
        </Field>

        <Field class="form-field form-field-toggle" name="emailVisibility" let:uniqueId>
            <input type="checkbox" id={uniqueId} bind:checked={user.emailVisibility} />
            <label for={uniqueId}>
                <span class="txt">Public visible email</span>
                <i
                    class="iconoir-info-circle link-hint"
                    use:tooltip={"By default other users can see only your avatar, name and username."} />
            </label>
        </Field>

        <Field class="form-field form-field-toggle" name="allowEmailNotifications" let:uniqueId>
            <input type="checkbox" id={uniqueId} bind:checked={user.allowEmailNotifications} />
            <label for={uniqueId}>
                <span class="txt">Enable email notifications for watched projects</span>
            </label>
        </Field>
    </form>

    <svelte:fragment slot="footer">
        <div class="inline-flex m-r-auto">
            <button
                type="button"
                class="btn btn-sm btn-circle btn-transparent"
                disabled={isSaving}
                aria-label="More">
                <i class="iconoir-more-horiz txt-lg" />

                <Toggler class="dropdown dropdown-upside dropdown-nowrap dropdown-left dropdown-sm">
                    <div tabindex="0" class="dropdown-item closable" on:click={requestPasswordReset}>
                        <i class="iconoir-send" />
                        <span class="txt">Password change request</span>
                    </div>
                    <hr />
                    <div tabindex="0" class="dropdown-item closable link-danger" on:click={deleteWithConfirm}>
                        <i class="iconoir-warning-triangle" />
                        <span class="txt">Delete account</span>
                    </div>
                </Toggler>
            </button>
        </div>

        <button type="button" class="btn btn-transparent" disabled={!canHide} on:click={hide}>
            <span class="txt">Cancel</span>
        </button>

        <button
            form={formId}
            type="submit"
            class="btn btn-expanded-sm btn-primary"
            class:btn-loading={isSaving}
            disabled={isSaving || !canHide || !hasChanges}>
            <span class="txt">Save changes</span>
        </button>
    </svelte:fragment>
</OverlayPanel>

<ChangeEmailPanel bind:this={changeEmailPanel} />

<style>
    .btn-close {
        position: absolute;
        right: -3px;
        top: -3px;
    }
</style>
