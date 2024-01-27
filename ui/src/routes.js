import { wrap } from "svelte-spa-router/wrap";
import pb from "@/pb";
import PageIndex from "@/components/PageIndex.svelte";
import PageLogin from "@/components/PageLogin.svelte";
import PageRegister from "@/components/PageRegister.svelte";
import PageProjects from "@/components/PageProjects.svelte";
import PageProject from "@/components/PageProject.svelte";
import PageScreen from "@/components/PageScreen.svelte";
import PageLink from "@/components/PageLink.svelte";

const routes = new Map();

routes.set(
    "/login",
    wrap({
        component: PageLogin,
        conditions: [(_) => !pb.authStore.isValid],
    }),
);

routes.set(
    "/register",
    wrap({
        component: PageRegister,
        conditions: [(_) => !pb.authStore.isValid],
    }),
);

routes.set(
    "/confirm-verification/:token",
    wrap({
        asyncComponent: () => import("@/components/PageConfirmVerification.svelte"),
    }),
);

routes.set(
    "/forgotten-password",
    wrap({
        asyncComponent: () => import("@/components/PageRequestPasswordReset.svelte"),
        conditions: [(_) => !pb.authStore.isValid],
    }),
);

routes.set(
    "/confirm-password-reset/:token",
    wrap({
        asyncComponent: () => import("@/components/PageConfirmPasswordReset.svelte"),
    }),
);

routes.set(
    "/confirm-email-change/:token",
    wrap({
        asyncComponent: () => import("@/components/PageConfirmEmailChange.svelte"),
    }),
);

routes.set(
    "/projects",
    wrap({
        component: PageProjects,
        conditions: [(_) => pb.authStore.isValid],
    }),
);

routes.set(
    "/projects/:projectId/prototypes/:prototypeId?",
    wrap({
        component: PageProject,
        conditions: [(_) => pb.authStore.isValid],
    }),
);

routes.set(
    "/projects/:projectId/prototypes/:prototypeId/screens/:screenId?",
    wrap({
        component: PageScreen,
        conditions: [(_) => pb.authStore.isValid],
    }),
);

// "/:linkSlug"
// "/:linkSlug/"
// "/:linkSlug/prototypes/:prototypeId"
// "/:linkSlug/prototypes/:prototypeId/screens/:screenId?"
routes.set(
    /^\/(?<linkSlug>[\w\.\-]+)(\/?|(\/prototypes\/(?<prototypeId>\w+)(\/screens\/(?<screenId>\w+))?))$/,
    wrap({
        component: PageLink,
    }),
);

// catch-all fallback
routes.set(
    "*",
    wrap({
        component: PageIndex,
    }),
);

export default routes;
