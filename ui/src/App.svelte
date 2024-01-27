<script>
    import "./scss/main.scss";

    import { onMount } from "svelte";
    import Router, { replace } from "svelte-spa-router";
    import routes from "./routes";
    import { options } from "@/stores/app";
    import { setErrors } from "@/stores/errors";
    import { resetConfirmation } from "@/stores/confirmation";
    import { loadNotifications, notificationsUnsubFunc } from "@/stores/notifications";
    import pb from "@/pb";
    import Toasts from "@/components/base/Toasts.svelte";
    import Confirmation from "@/components/base/Confirmation.svelte";

    let oldLocation;

    function handleRouteLoading(e) {
        if (e.detail.location === oldLocation) {
            return; // not an actual change
        }

        oldLocation = e.detail.location;

        // resets
        setErrors({});
        resetConfirmation();
    }

    function handleRouteFailure() {
        if (!pb.authStore.isValid) {
            pb.rememberPath();
        }

        replace("/");
    }

    async function loadOptions() {
        try {
            $options = await pb.send("/api/pr/options");
        } catch (err) {
            console.log("failed to load options:", err);
        }
    }

    loadOptions();

    onMount(() => {
        // note: the change will be triggered automatically by the initial user refresh call
        pb.authStore.onChange((token) => {
            if (token) {
                loadNotifications();
            } else {
                notificationsUnsubFunc?.();
            }
        });
    });
</script>

<Router {routes} on:routeLoading={handleRouteLoading} on:conditionsFailed={handleRouteFailure} />

<Toasts />

<Confirmation />
