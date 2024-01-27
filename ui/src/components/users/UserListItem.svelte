<script>
    import { loggedUser } from "@/stores/app";
    import UserThumb from "@/components/users/UserThumb.svelte";

    export let user = {};

    let classes = "";
    export { classes as class }; // export reserved keyword

    $: isLoggedUser = $loggedUser?.id == user.id;
</script>

<div class="list-item {classes}">
    <UserThumb {user} />

    <div class="content">
        {#if user.name || isLoggedUser}
            <div class="row">
                {#if user.name}
                    <span class="user-name txt-ellipsis" title={user.name}>
                        {user.name}
                    </span>
                {/if}
                {#if isLoggedUser}
                    <span class="label label-sm label-warning">You</span>
                {/if}
            </div>
        {/if}
        <div class="row txt-hint">
            <small class="user-username txt-ellipsis" title="@{user.username}">
                @{user.username}
            </small>

            {#if user.email}
                <small>|</small>
                <small class="user-email txt-ellipsis" title={user.email}>
                    {user.email}
                </small>
            {/if}
        </div>
    </div>

    <slot />
</div>
