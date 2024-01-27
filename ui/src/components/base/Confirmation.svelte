<script>
    import { tick } from "svelte";
    import OverlayPanel from "@/components/base/OverlayPanel.svelte";
    import { confirmation, resetConfirmation } from "@/stores/confirmation";

    let confirmationPopup;
    let isConfirmationBusy = false;
    let confirmed = false;

    $: if ($confirmation?.text) {
        confirmed = false;
        confirmationPopup?.show();
    }
</script>

<OverlayPanel
    bind:this={confirmationPopup}
    class="confirm-popup hide-content overlay-panel-sm"
    overlayClose={!isConfirmationBusy}
    escHide={!isConfirmationBusy}
    btnClose={false}
    popup
    on:hide={async () => {
        if (!confirmed && $confirmation?.noCallback) {
            $confirmation.noCallback();
        }
        await tick();
        confirmed = false;
        resetConfirmation();
    }}
>
    <h4 class="block center txt-break" slot="header">{$confirmation?.text}</h4>

    <svelte:fragment slot="footer">
        <div class="grid">
            <div class="col-6">
                <!-- svelte-ignore a11y-autofocus -->
                <button
                    autofocus
                    type="button"
                    class="btn btn-semitransparent btn-block"
                    disabled={isConfirmationBusy}
                    on:click={() => {
                        confirmed = false;
                        confirmationPopup?.hide();
                    }}
                >
                    <span class="txt">No</span>
                </button>
            </div>
            <div class="col-6">
                <button
                    type="button"
                    class="btn btn-warning btn-block"
                    class:btn-loading={isConfirmationBusy}
                    disabled={isConfirmationBusy}
                    on:click={async () => {
                        if ($confirmation?.yesCallback) {
                            isConfirmationBusy = true;
                            await Promise.resolve($confirmation.yesCallback());
                            isConfirmationBusy = false;
                        }
                        confirmed = true;
                        confirmationPopup?.hide();
                    }}
                >
                    <span class="txt">Yes</span>
                </button>
            </div>
        </div>
    </svelte:fragment>
</OverlayPanel>
