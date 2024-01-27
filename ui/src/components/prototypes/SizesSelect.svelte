<script>
    import tooltip from "@/actions/tooltip";
    import ObjectSelect from "@/components/base/ObjectSelect.svelte";
    import SizeOption from "@/components/prototypes/SizeOption.svelte";

    export let id;
    export let options = [];
    export let value = options?.[0]?.value || "";

    let oldValue = value;
    let option = "";
    let width = 0;
    let height = 0;

    // update selected option on value change
    $: if (oldValue != value) {
        refreshOption();
        setSize(value);
    }

    // reload selected option on options change
    $: if (options) {
        refreshOption();
    }

    // update size on option change
    $: if (option) {
        setSize(option);
    }

    // set size on custom width and height change
    $: if (width || height) {
        setSize(`${width}x${height}`);
    }

    function setSize(newSize) {
        oldValue = newSize;
        value = newSize;

        refreshWidthAndHeight();
    }

    function refreshOption() {
        option = options.find((o) => o.value == value)?.value || "";

        refreshWidthAndHeight();
    }

    function refreshWidthAndHeight() {
        const parts = value.split("x");
        width = parts?.[0] << 0;
        height = parts?.[1] << 0;
    }
</script>

<div class="grid grid-sm">
    <div class="col-{option == '' ? 6 : 12}">
        <ObjectSelect
            {id}
            upside
            labelComponent={SizeOption}
            optionComponent={SizeOption}
            items={options}
            bind:keyOfSelected={option}
        />
    </div>
    {#if option == ""}
        <div class="col-3" use:tooltip={"Width"}>
            <input type="number" min="1" placeholder="Width" required bind:value={width} />
        </div>
        <div class="col-3" use:tooltip={"Height"}>
            <input type="number" min="1" placeholder="Height" required bind:value={height} />
        </div>
    {/if}
</div>
