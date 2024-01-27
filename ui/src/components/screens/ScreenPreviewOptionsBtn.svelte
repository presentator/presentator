<script>
    import tooltip from "@/actions/tooltip";
    import Toggler from "@/components/base/Toggler.svelte";
    import Field from "@/components/base/Field.svelte";
    import { showPreviewHotspots, showPreviewAnnotations } from "@/stores/screens";
    import { activeScreenHotspots, hotspotTypes } from "@/stores/hotspots";

    let togglerActive = false;

    let hotspotsCheckbox;
    let annotationsCheckbox;

    $: totalHotspots = $activeScreenHotspots?.filter((h) => h.type != hotspotTypes.note)?.length || 0;

    $: totalAnnotations = $activeScreenHotspots?.filter((h) => h.type == hotspotTypes.note)?.length || 0;
</script>

<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<!-- svelte-ignore a11y-no-static-element-interactions -->
<!-- svelte-ignore a11y-click-events-have-key-events -->
<div
    tabindex="0"
    type="button"
    class="btn btn-circle btn-hint txt-hint btn-transparent entrance-right"
    use:tooltip={!togglerActive ? { position: "top", text: "Preview options" } : undefined}
>
    <i class="iconoir-switch-on" />
    <Toggler class="dropdown dropdown-upside dropdown-nowrap" bind:active={togglerActive}>
        <div class="dropdown-item" on:click|stopPropagation={() => (hotspotsCheckbox.checked ^= true)}>
            <Field class="form-field form-field-sm form-field-toggle m-0" let:uniqueId>
                <input
                    bind:this={hotspotsCheckbox}
                    type="checkbox"
                    id={uniqueId}
                    bind:checked={$showPreviewHotspots}
                />
                <label for={uniqueId}>
                    Show hotspots ({totalHotspots})
                </label>
            </Field>
        </div>
        <div class="dropdown-item" on:click|stopPropagation={() => (annotationsCheckbox.checked ^= true)}>
            <Field class="form-field form-field-sm form-field-toggle m-0" let:uniqueId>
                <input
                    bind:this={annotationsCheckbox}
                    type="checkbox"
                    id={uniqueId}
                    bind:checked={$showPreviewAnnotations}
                />
                <label for={uniqueId}>
                    Show notes ({totalAnnotations})
                </label>
            </Field>
        </div>
    </Toggler>
</div>
