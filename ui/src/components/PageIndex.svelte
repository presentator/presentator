<script>
    import { replace } from "svelte-spa-router";
    import pb from "@/pb";

    handler();

    function handler() {
        if (pb.authStore.isValid) {
            replace("/projects");
        } else {
            pb.logout();
        }
    }
</script>
