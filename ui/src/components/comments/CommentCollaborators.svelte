<script>
    import { createEventDispatcher, onDestroy } from "svelte";
    import utils from "@/utils";

    const dispatch = createEventDispatcher();

    export let input;
    export let list = []; // array list of options in the format [{label, value}]
    export let triggers = ["+", "@"];

    let active = false;
    let focusIndex = 0;
    let elems = [];
    let filtered = [];

    $: if (input) {
        initEvents();
    }

    export function show() {
        if (active) {
            return;
        }

        active = true;
        focusIndex = 0;

        dispatch("show");
    }

    export function hide() {
        if (!active) {
            return; // already hidden
        }

        active = false;

        dispatch("hide");
    }

    function select(index) {
        index = typeof index !== "undefined" ? index : focusIndex;
        let wordInfo = getCurrentWordInfo();

        if (!wordInfo.word || !input || !filtered[index]) {
            return;
        }

        let val = input.value;

        input.value =
            val.substring(0, wordInfo.start) +
            triggers[0] +
            filtered[index].value +
            val.substring(wordInfo.end + 1) +
            " ";

        input.dispatchEvent(new Event("input"));

        input.focus();
        hide();
    }

    function focusPrev() {
        if (focusIndex <= 0) {
            focusIndex = filtered.length - 1;
        } else {
            focusIndex--;
        }

        positionFocusedWithinView();
    }

    function focusNext() {
        if (focusIndex >= filtered.length - 1) {
            focusIndex = 0;
        } else {
            focusIndex++;
        }

        positionFocusedWithinView();
    }

    function positionFocusedWithinView() {
        elems[focusIndex]?.scrollIntoView({
            block: "nearest",
        });
    }

    function getCurrentWordInfo() {
        const cursorPos =
            typeof input.selectionStart !== "undefined" ? input.selectionStart : input.value.length;

        return utils.getWordInfoAt(input.value, cursorPos - 1);
    }

    function onInputChange() {
        const wordInfo = getCurrentWordInfo();

        // reset
        hide();

        if (wordInfo.word && triggers.indexOf(wordInfo.word?.[0]) >= 0) {
            filtered = filterList(wordInfo.word.substring(1));
            if (filtered.length) {
                show();
            }
        }
    }

    function filterList(search) {
        search = (search || "").toLowerCase().replace(/\s+/g, "");
        if (!search) {
            return list;
        }

        const result = [];

        for (let i = list.length - 1; i >= 0; i--) {
            let identifier = (list[i].value + list[i].label || "").toLowerCase().replace(/\s+/g, "");

            if (identifier.indexOf(search) >= 0) {
                result.push(list[i]);
            }
        }

        return result;
    }

    function onInputKeydown(e) {
        if (!active) {
            return;
        }

        let code = e.code;

        if (code == "Enter") {
            e.preventDefault();
            select();
        } else if (code == "ArrowUp") {
            e.preventDefault();
            focusPrev();
        } else if (code == "ArrowDown") {
            e.preventDefault();
            focusNext();
        }
    }

    function clearEvents() {
        input?.removeEventListener("input", onInputChange);
        input?.removeEventListener("keydown", onInputKeydown);
    }

    function initEvents() {
        clearEvents();

        input.addEventListener("input", onInputChange);
        input.addEventListener("keydown", onInputKeydown);
    }

    onDestroy(() => {
        clearEvents();
    });
</script>

{#if active}
    <div class="dropdown dropdown-sm collaborators-dropdown">
        {#each filtered as item, i}
            <!-- svelte-ignore a11y-no-static-element-interactions -->
            <!-- svelte-ignore a11y-click-events-have-key-events -->
            <div
                bind:this={elems[i]}
                class="dropdown-item"
                class:focused={i == focusIndex}
                on:click|preventDefault={() => select(i)}
            >
                +{item.label}
            </div>
        {/each}
    </div>
{/if}
