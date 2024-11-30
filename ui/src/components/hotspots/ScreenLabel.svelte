<script>
    import pb from "@/pb";
    import LazyImg from "@/components/base/LazyImg.svelte";

    export let item = {};
</script>

<div class="thumb thumb-xs">
    <LazyImg src={pb.files.getURL(item, item.file, { thumb: "100x100t" })} alt={item.title} />
</div>
<span class="txt title m-l-5">{item.title}</span>
