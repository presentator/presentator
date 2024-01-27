<script>
    import { fade, slide } from "svelte/transition";
    import { flip } from "svelte/animate";
    import { toasts, removeToast } from "@/stores/toasts";
</script>

<div class="toasts-wrapper">
    {#each $toasts as toast (toast.message)}
        <div
            class="alert txt-break"
            class:alert-info={toast.type == "info"}
            class:alert-success={toast.type == "success"}
            class:alert-danger={toast.type == "error"}
            class:alert-warning={toast.type == "warning"}
            in:slide={{ duration: 150 }}
            out:fade={{ duration: 150 }}
            animate:flip={{ duration: 150 }}
        >
            <div class="icon">
                {#if toast.type === "info"}
                    <i class="iconoir-info-circle" />
                {:else if toast.type === "success"}
                    <i class="iconoir-check-circle" />
                {:else if toast.type === "warning"}
                    <i class="iconoir-warning-circle" />
                {:else}
                    <i class="iconoir-warning-triangle" />
                {/if}
            </div>

            <div class="content">{toast.message}</div>

            <button type="button" class="close" on:click|preventDefault={() => removeToast(toast)}>
                <i class="iconoir-xmark" />
            </button>
        </div>
    {/each}
</div>
