<script>
    import tooltip from "@/actions/tooltip";
    import { activeProject } from "@/stores/projects";
    import Toggler from "@/components/base/Toggler.svelte";
    import ReportPanel from "@/components/links/ReportPanel.svelte";

    let panel;
    let reportPanel;
    let togglerActive = false;

    export function show() {
        panel?.show();
    }

    export function hide() {
        panel?.hide();
    }
</script>

<!-- svelte-ignore a11y-no-noninteractive-tabindex -->
<div
    tabindex="0"
    class="btn btn-circle btn-hint txt-hint btn-transparent project-info-btn"
    use:tooltip={!togglerActive ? { position: "top-right", text: "Project info" } : undefined}
    on:click|stopPropagation
>
    <i class="iconoir-info-circle" />

    <Toggler
        bind:this={panel}
        bind:active={togglerActive}
        class="dropdown dropdown-right dropdown-upside project-info-dropdown"
    >
        <div class="content">
            <p class="txt-bold">{$activeProject.title}</p>

            <button
                type="button"
                class="btn btn-xs btn-transparent btn-danger m-t-xs"
                on:click|stopPropagation={() => reportPanel?.show()}
            >
                <i class="iconoir-warning-triangle-solid" />
                <span class="txt">Report design</span>
            </button>

            <hr class="m-t-xs m-b-xs" />

            <p class="txt-xs txt-hint">
                Presented with
                <a href={import.meta.env.PR_SITE_URL} target="_blank" rel="noopener noreferrer">
                    Presentator
                </a>
            </p>
        </div>
    </Toggler>
</div>

<ReportPanel bind:this={reportPanel} />

<style lang="scss">
    :global(.project-info-dropdown) {
        min-width: 250px;
        padding: var(--smSpacing);
        color: var(--txtBaseColor);
        font-size: 1rem;
        font-weight: normal;
        text-align: center;
    }
</style>
