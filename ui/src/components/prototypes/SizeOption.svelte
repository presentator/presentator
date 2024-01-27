<script>
    export let item = {};
</script>

<div class="flex">
    <span class="txt">{item.label}</span>
    {#if item.value}
        <small class="txt-hint m-l-auto">{item.value}</small>
    {/if}
</div>
