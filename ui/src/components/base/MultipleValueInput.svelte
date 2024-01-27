<script>
    import utils from "@/utils";

    export let value = [];
    export let separator = ",";
    export let readonly = null;
    export let disabled = null;

    $: valueStr = utils.joinNonEmpty(value, separator + " ");
</script>

<input
    type={$$restProps.type || "text"}
    value={valueStr}
    {disabled}
    {readonly}
    on:input={(e) => {
        value = utils.splitNonEmpty(e.target.value, separator);
    }}
    {...$$restProps}
/>
