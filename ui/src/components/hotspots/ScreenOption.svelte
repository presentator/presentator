<script>
    import pb from "@/pb";
    import tooltip from "@/actions/tooltip";
    import LazyImg from "@/components/base/LazyImg.svelte";

    export let item = {};
</script>

<div class="thumb thumb-handle" use:tooltip={item.title}>
    <LazyImg src={pb.files.getURL(item, item.file, { thumb: "100x100t" })} alt={item.title} />
</div>
