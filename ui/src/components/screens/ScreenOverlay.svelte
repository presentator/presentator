<script>
    import { onDestroy } from "svelte";
    import pb from "@/pb.js";
    import { templates } from "@/stores/templates.js";
    import { hotspots, filterHotspots } from "@/stores/hotspots.js";
    import {
        screens,
        activeScale,
        mode,
        modes,
        fitToScreen,
        overlayScreenSettings,
    } from "@/stores/screens.js";
    import LazyImg from "@/components/base/LazyImg.svelte";
    import Hotspot from "@/components/hotspots/Hotspot.svelte";

    let img = null;
    let screen = null;
    let active = false;
    let overlayScale = null;
    let scaleTimeout = null;

    $: if ($overlayScreenSettings != -1) {
        onOverlayScreenSettingsChange();
    }

    $: screenHotspots = screen?.id ? filterHotspots(screen, $hotspots, $templates) : [];

    $: if ($fitToScreen !== -1) {
        updateOverlayScale();
    }

    function onOverlayScreenSettingsChange() {
        screen = $overlayScreenSettings?.screen
            ? $screens.find((s) => s.id == $overlayScreenSettings.screen)
            : null;

        if (!screen) {
            hide();
        } else {
            active = true;
        }
    }

    function hide() {
        active = false;

        if ($overlayScreenSettings) {
            $overlayScreenSettings = null;
        }

        if (screen) {
            screen = null;
        }
    }

    function updateOverlayScale(timeout = 250) {
        if (!active) {
            return;
        }

        if (scaleTimeout) {
            clearTimeout(scaleTimeout);
        }

        scaleTimeout = setTimeout(() => {
            overlayScale = img ? img.widthRatio() : null;
            scaleTimeout = null;
        }, timeout);
    }

    onDestroy(() => {
        if (scaleTimeout) {
            clearTimeout(scaleTimeout);
        }
    });
</script>

<svelte:window on:resize={() => updateOverlayScale()} />

{#if active && $overlayScreenSettings}
    <!-- svelte-ignore a11y-click-events-have-key-events -->
    <!-- svelte-ignore a11y-no-static-element-interactions -->
    <div
        class="screen-overlay-container"
        on:click={function (e) {
            if ($overlayScreenSettings.outsideClose && e.target == this) {
                hide();
            }
        }}
    >
        <div
            class="screen-overlay
                {$overlayScreenSettings.fixOverlay ? 'fixed' : ''}
                transition-{$overlayScreenSettings.transition || 'none'}
                position-{$overlayScreenSettings.overlayPosition}"
        >
            <div
                class="screen-overlay-offset"
                style:margin-top="{$overlayScreenSettings.offsetTop * $activeScale}px"
                style:margin-left="{$overlayScreenSettings.offsetLeft * $activeScale}px"
                style:margin-right="{$overlayScreenSettings.offseRight * $activeScale}px"
                style:margin-bottom="{$overlayScreenSettings.offsetBottom * $activeScale}px"
            >
                <LazyImg
                    bind:this={img}
                    src={pb.files.getUrl(screen, screen.file)}
                    alt={screen.title}
                    draggable={false}
                    loading="eager"
                    loaderClass="hidden"
                    fetchpriority="high"
                    scale={$activeScale}
                    on:load={updateOverlayScale}
                />
                <div class="hotspots">
                    {#each screenHotspots as hotspot (hotspot.id)}
                        <Hotspot preview {hotspot} scale={overlayScale} backFunc={hide} />
                    {/each}
                </div>
            </div>
        </div>
    </div>
{/if}
