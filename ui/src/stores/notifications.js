import { writable, derived } from "svelte/store";
import pb from "@/pb";
import utils from "@/utils";
import { activeScreen } from "@/stores/screens";

export const notifications = writable([]);

export const activeScreenNotifications = derived(
    [activeScreen, notifications],
    ([$activeScreen, $notifications]) => {
        if (!$activeScreen?.id) {
            return [];
        }

        return $notifications.filter((n) => n.expand?.comment?.screen == $activeScreen.id);
    },
);

export function resetNotificationsStore() {
    notifications.set([]);
}

export function removeNotification(notificationOrId) {
    notifications.update((list) => {
        utils.removeByKey(list, "id", notificationOrId?.id || notificationOrId);
        return list;
    });
}

export function addNotification(notification) {
    if (notification.read) {
        return; // already read
    }

    notifications.update((list) => {
        utils.pushOrReplaceObject(list, notification);
        return list;
    });
}

const expand = "comment.screen.prototype.project,comment.user";

// load unread notifications and init the notification subscriptions
export async function loadNotifications() {
    try {
        const unreadList = await pb.collection("notifications").getFullList({
            filter: `read=false`,
            expand: expand,
        });

        notifications.set(unreadList);

        return initSubscription();
    } catch (err) {
        if (!err.isAbort) {
            console.warn("loadNotifications:", err);
        }
    }
}

export let notificationsUnsubFunc;

async function initSubscription() {
    // subscribe to new notifications
    notificationsUnsubFunc?.();

    return pb
        .collection("notifications")
        .subscribe(
            "*",
            async (e) => {
                if (e.action == "delete" || e.record.read) {
                    removeNotification(e.record);
                } else {
                    addNotification(e.record);
                }
            },
            {
                expand: expand,
            },
        )
        .then((unsubscribe) => {
            notificationsUnsubFunc = unsubscribe;
            return unsubscribe;
        })
        .catch((err) => {
            if (!err.isAbort) {
                console.log("notifications subscription failed:", err);
            }
        });
}
