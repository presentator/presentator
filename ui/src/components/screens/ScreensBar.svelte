<script>
    import { tick } from "svelte";
    import { slide } from "svelte/transition";
    import pb from "@/pb";
    import { activeScreen, screens } from "@/stores/screens";
    import tooltip from "@/actions/tooltip";
    import LazyImg from "@/components/base/LazyImg.svelte";
    import { onMount } from "svelte";

    export let active = true;

    let classes = "";
    export { classes as class }; // export reserved keyword

    $: if (active && $activeScreen?.id) {
        focusActiveScreen();
    }

    export function show() {
        active = true;
    }

    export function hide() {
        active = false;
    }

    export function toggle() {
        active ? hide() : show();
    }

    async function focusActiveScreen() {
        if (!active || !$activeScreen?.id) {
            return;
        }

        await tick();

        const thumb = document.querySelector(`[data-screen-id="${$activeScreen.id}"]`);
        if (!thumb) {
            return;
        }

        thumb.scrollIntoView();

        // change the focus **only** if the current focused element is one one of the thumbs
        if (document.activeElement && thumb.parentNode.contains(document.activeElement)) {
            thumb.focus();
        }
    }

    onMount(() => {
        focusActiveScreen();
    });
</script>

{#if active}
    <div class="screens-bar {classes}" transition:slide={{ duration: 150 }}>
        {#each $screens as screen (screen.id)}
            <button
                type="button"
                data-screen-id={screen.id}
                class="thumb thumb-handle"
                class:thumb-highlight-mode-accent={$activeScreen.id == screen.id}
                use:tooltip={{ position: "top", text: screen.title }}
                on:click|preventDefault|stopPropagation={() => {
                    $activeScreen = screen.id;
                }}
            >
                <LazyImg
                    src={pb.files.getURL(screen, screen.file, { thumb: "100x100" })}
                    alt={screen.title}
                />
            </button>
        {/each}
    </div>
{/if}
