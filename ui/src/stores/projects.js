import { writable, derived } from "svelte/store";
import utils from "@/utils";

export const projects = writable([]);
export const isLoadingProjects = writable(false);
export const activeProjectId = writable(""); // @see activeProject

export function resetProjectsStore() {
    projects.set([]);
    isLoadingProjects.set(false);
    activeProjectId.set("");
}

export const activeProject = derived([projects, activeProjectId], ([$projects, $activeProjectId]) => {
    if (!$projects.length) {
        return null;
    }

    // always fallback to the first project if no explicit active project is set
    return ($activeProjectId && $projects.find((p) => p.id == $activeProjectId)) || $projects[0];
});
activeProject.set = (modelOrId) => {
    if (utils.isObject(modelOrId)) {
        addProject(modelOrId, true);
    } else {
        activeProjectId.set(modelOrId);
    }
};

export function addProject(project, active = false) {
    if (!project) {
        return;
    }

    projects.update((list) => {
        utils.pushOrReplaceObject(list, project);
        return list;
    });

    if (active) {
        activeProjectId.set(project.id);
    }
}

export function removeProject(project) {
    if (!project) {
        return;
    }

    projects.update((list) => {
        utils.removeByKey(list, "id", project.id);
        return list;
    });

    activeProjectId.update((id) => {
        return id == project.id ? "" : id;
    });
}
