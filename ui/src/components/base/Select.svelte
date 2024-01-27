<script>
    import { onMount } from "svelte";
    import utils from "@/utils";
    import tooltip from "@/actions/tooltip";
    import Toggler from "@/components/base/Toggler.svelte";

    export let id = "";
    export let noOptionsText = "No options found";
    export let selectPlaceholder = "Select";
    export let searchPlaceholder = "Search...";
    export let items = [];
    export let multiple = false;
    export let disabled = false;
    export let readonly = false;
    export let upside = false;
    export let dropdownClass = ""; // extra dropdown classes
    export let selected = multiple ? [] : undefined;
    export let toggle = multiple; // toggle option on click
    export let closable = true; // close the dropdown on option select/deselect
    export let labelComponent = undefined; // custom component to use for each selected option label
    export let labelComponentProps = {}; // props to pass to the custom option component
    export let optionComponent = undefined; // custom component to use for each dropdown option item
    export let optionComponentProps = {}; // props to pass to the custom option component
    export let searchable = false; // whether to show the dropdown options search input
    export let searchFunc = undefined; // custom search option filter: `function(item, searchTerm):boolean`

    let classes = "";
    export { classes as class }; // export reserved keyword

    let toggler;
    let searchTerm = "";
    let container = undefined;
    let labelDiv = undefined;
    let togglerActive = false;

    $: if (items) {
        ensureSelectedExist();
        resetSearch();
    }

    $: filteredItems = filterItems(items, searchTerm);

    $: isSelected = function (item) {
        const normalized = utils.toArray(selected);

        return utils.inArray(normalized, item);
    };

    // Selection handlers
    // ---------------------------------------------------------------
    export function deselectItem(item) {
        if (utils.isEmpty(selected)) {
            return; // nothing to deselect
        }

        let normalized = utils.toArray(selected);
        if (utils.inArray(normalized, item)) {
            utils.removeByValue(normalized, item);
            selected = normalized;
        }

        // emulate native change event
        container?.dispatchEvent(new CustomEvent("change", { detail: selected, bubbles: true }));
    }

    export function selectItem(item) {
        if (multiple) {
            let normalized = utils.toArray(selected);
            if (!utils.inArray(normalized, item)) {
                selected = [...normalized, item];
            }
        } else {
            selected = item;
        }

        // emulate native change event
        container?.dispatchEvent(new CustomEvent("change", { detail: selected, bubbles: true }));
    }

    export function toggleItem(item) {
        return isSelected(item) ? deselectItem(item) : selectItem(item);
    }

    export function reset() {
        selected = multiple ? [] : undefined;
    }

    export function showDropdown() {
        toggler?.show && toggler?.show();
    }

    export function hideDropdown() {
        toggler?.hide && toggler?.hide();
    }

    function ensureSelectedExist() {
        if (utils.isEmpty(selected) || utils.isEmpty(items)) {
            return; // nothing to check
        }

        let selectedArray = utils.toArray(selected);
        let unselectedArray = [];

        // find missing
        for (const selectedItem of selectedArray) {
            if (!utils.inArray(items, selectedItem)) {
                unselectedArray.push(selectedItem);
            }
        }

        // trigger reactivity
        if (unselectedArray.length) {
            for (const item of unselectedArray) {
                utils.removeByValue(selectedArray, item);
            }

            selected = multiple ? selectedArray : selectedArray[0];
        }
    }

    // Search handlers
    // ---------------------------------------------------------------
    function defaultSearchFunc(item, search) {
        let normalizedSearch = ("" + search).replace(/\s+/g, "").toLowerCase();
        let normalizedItem = item;

        try {
            if (typeof item === "object" && item !== null) {
                normalizedItem = JSON.stringify(item);
            }
        } catch (e) {}

        return ("" + normalizedItem).replace(/\s+/g, "").toLowerCase().includes(normalizedSearch);
    }

    function resetSearch() {
        searchTerm = "";
    }

    function filterItems(items, search) {
        items = items || [];

        const filterFunc = searchFunc || defaultSearchFunc;

        return items.filter((item) => filterFunc(item, search)) || [];
    }

    // Option actions
    // ---------------------------------------------------------------
    function handleOptionSelect(e, item) {
        e.preventDefault();

        if (toggle && multiple) {
            toggleItem(item);
        } else {
            selectItem(item);
        }
    }

    function handleOptionKeypress(e, item) {
        if (e.code === "Enter" || e.code === "Space") {
            handleOptionSelect(e, item);
            if (closable) {
                hideDropdown();
            }
        }
    }

    function onDropdownShow() {
        resetSearch();

        // ensure that the first selected option is visible
        setTimeout(() => {
            const selected = container?.querySelector(".dropdown-item.option.selected");
            if (selected) {
                selected.focus();
                selected.scrollIntoView({ block: "nearest" });
            }
        }, 0);
    }

    // Label(s) activation
    // ---------------------------------------------------------------
    function onLabelClick(e) {
        e.stopPropagation();

        !readonly && !disabled && toggler?.toggle();
    }

    onMount(() => {
        const labels = document.querySelectorAll(`label[for="${id}"]`);

        for (const label of labels) {
            label.addEventListener("click", onLabelClick);
        }

        return () => {
            for (const label of labels) {
                label.removeEventListener("click", onLabelClick);
            }
        };
    });
</script>

<div
    bind:this={container}
    class="select {classes}"
    class:active={togglerActive}
    class:upside
    class:multiple
    class:disabled
    class:readonly
>
    <!-- svelte-ignore a11y-no-noninteractive-tabindex -->
    <div
        bind:this={labelDiv}
        tabindex={disabled || readonly ? "-1" : "0"}
        class="selected-container"
        class:disabled
        class:readonly
    >
        {#each utils.toArray(selected) as item, i}
            <div class="option">
                {#if labelComponent}
                    <svelte:component this={labelComponent} {item} {...labelComponentProps} />
                {:else}
                    <span class="txt">{item}</span>
                {/if}

                {#if multiple || toggle}
                    <!-- svelte-ignore a11y-click-events-have-key-events -->
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <span
                        class="clear"
                        use:tooltip={"Clear"}
                        on:click|preventDefault|stopPropagation={() => deselectItem(item)}
                    >
                        <i class="iconoir-xmark" />
                    </span>
                {/if}
            </div>
        {:else}
            <div class="block txt-placeholder" class:link-hint={!disabled && !readonly}>
                {selectPlaceholder}
            </div>
        {/each}
    </div>

    {#if !disabled && !readonly}
        <Toggler
            bind:this={toggler}
            bind:active={togglerActive}
            class="dropdown dropdown-block options-dropdown {dropdownClass} {upside ? 'dropdown-upside' : ''}"
            trigger={labelDiv}
            on:show={onDropdownShow}
            on:hide
        >
            {#if searchable}
                <div class="form-field form-field-sm options-search">
                    <label class="field-group">
                        <div class="addon">
                            <i class="iconoir-search" />
                        </div>
                        <!-- svelte-ignore a11y-autofocus -->
                        <input
                            autofocus
                            type="text"
                            placeholder={searchPlaceholder}
                            bind:value={searchTerm}
                        />

                        {#if searchTerm.length}
                            <div class="addon suffix">
                                <button
                                    type="button"
                                    class="btn btn-xs btn-circle btn-hint btn-transparent clear"
                                    on:click|preventDefault|stopPropagation={resetSearch}
                                >
                                    <i class="iconoir-xmark" />
                                </button>
                            </div>
                        {/if}
                    </label>
                </div>
            {/if}

            <slot name="beforeOptions" />

            <div class="options-list">
                {#each filteredItems as item}
                    <!-- svelte-ignore a11y-no-noninteractive-tabindex -->
                    <!-- svelte-ignore a11y-no-static-element-interactions -->
                    <div
                        tabindex="0"
                        class="dropdown-item option"
                        class:closable
                        class:selected={isSelected(item)}
                        on:click={(e) => handleOptionSelect(e, item)}
                        on:keydown={(e) => handleOptionKeypress(e, item)}
                    >
                        {#if optionComponent}
                            <svelte:component this={optionComponent} {item} {...optionComponentProps} />
                        {:else}{item}{/if}
                    </div>
                {:else}
                    {#if noOptionsText}
                        <div class="txt-missing">{noOptionsText}</div>
                    {/if}
                {/each}
            </div>

            <slot name="afterOptions" />
        </Toggler>
    {/if}
</div>
