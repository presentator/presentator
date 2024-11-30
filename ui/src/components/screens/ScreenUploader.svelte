<script>
    import { createEventDispatcher } from "svelte";
    import pb from "@/pb";
    import utils from "@/utils";
    import { addWarningToast } from "@/stores/toasts";
    import { activePrototype, addPrototype } from "@/stores/prototypes";
    import { addScreen, screens } from "@/stores/screens";
    import Droppable from "@/components/base/Droppable.svelte";

    const dispatch = createEventDispatcher();

    export let id = "uploader_" + utils.randomString(5);

    let input;
    let isUploading = false;

    async function upload(filesList) {
        if (isUploading || !$activePrototype?.id || !filesList?.length) {
            return;
        }

        isUploading = true;

        const promises = [];
        let uploadedOrderedIds = [];

        for (let i = 0; i < filesList.length; i++) {
            const file = filesList.item(i);

            let id = "s" + i;
            id += utils.randomString(15 - id.length);

            const lastScreen = $screens?.[$screens.length - 1];

            const data = new FormData();
            data.append("id", id);
            data.append("file", file);
            data.append("title", file.name.split(".").slice(0, -1).join(".") || file.name); // trim extension
            data.append("prototype", $activePrototype.id);
            // copy some of the last screen settings (if any)
            data.append("background", lastScreen?.background || "");
            data.append("alignment", lastScreen?.alignment || "");

            promises.push(pb.collection("screens").create(data, { requestKey: null }));

            uploadedOrderedIds.push(id);
        }

        const failed = [];
        const uploaded = {};

        try {
            const results = await Promise.allSettled(promises);

            for (const result of results) {
                if (result.status === "fulfilled") {
                    uploaded[result.value.id] = result.value;
                    addScreen(result.value);
                } else {
                    failed.push(result);
                }
            }

            // update uploadedOrderedIds with only the successfully uploaded ones
            uploadedOrderedIds = uploadedOrderedIds.filter((id) => !!uploaded[id]);

            const prototype = await pb.collection("prototypes").update(
                $activePrototype.id,
                {
                    screensOrder: $activePrototype.screensOrder.concat(uploadedOrderedIds),
                },
                {
                    $autoCancel: false,
                },
            );
            addPrototype(prototype);

            dispatch("upload", Object.values(uploaded));

            // reset file input
            if (input) {
                input.value = "";
            }
        } catch (err) {
            console.warn("screens upload error:", err);
        }

        if (failed.length > 0) {
            console.warn("failed to upload:", failed);
            addWarningToast("Failed to upload some of the selected files.");
        }

        isUploading = false;
    }
</script>

<svelte:window
    on:drop|preventDefault={(e) => {
        upload(e.dataTransfer.files);
    }}
    on:dragover|preventDefault|stopPropagation
/>

<Droppable
    let:dragover
    on:drop={(e) => {
        upload(e.detail);
    }}
>
    <input
        bind:this={input}
        multiple
        {id}
        type="file"
        accept="image/*"
        class="hidden"
        on:change={(e) => upload(e.target.files)}
    />
    <slot uniqueId={id} {isUploading} {dragover} />
</Droppable>
