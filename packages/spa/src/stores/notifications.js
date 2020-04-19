import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import ScreenComment from '@/models/ScreenComment';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            unreadComments: [],
        }
    },
    mutations: {
        setUnreadComments(state, commentsData) {
            state.unreadComments = ScreenComment.createInstances(commentsData);
        },
        addUnreadComment(state, commentData) {
            CommonHelper.pushUnique(state.unreadComments, new ScreenComment(commentData));
        },
        removeUnreadComment(state, commentId) {
            CommonHelper.removeByKey(state.unreadComments, 'id', commentId);
        },
    },
    actions: {
        setUnreadComments(context, commentsData) {
            context.commit('setUnreadComments', commentsData);
        },
        addUnreadComment(context, commentData) {
            context.commit('addUnreadComment', commentData);
        },
        removeUnreadComment(context, commentId) {
            context.commit('removeUnreadComment', commentId);
        },
        loadUserUnreadComments(context) {
            return new Promise((resolve, reject) => {
                if (!ApiClient.$token) {
                    reject(new Error('The action requires authorization token to be set.'));
                } else {
                    ApiClient.ScreenComments.getUnread().then((response) => {
                        context.dispatch('setUnreadComments', response.data);

                        resolve(response);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },
        markAsRead(context, commentId) {
            return new Promise((resolve, reject) => {
                if (!ApiClient.$token) {
                    reject(new Error('The action requires authorization token to be set.'));
                } else {
                    // optimistic update
                    context.dispatch('removeUnreadComment', commentId);

                    // actual update
                    ApiClient.ScreenComments.read(commentId).then((response) => {
                        resolve(response);
                    }).catch((err) => {
                        reject(err);
                    });
                }
            });
        },
    },
    getters: {
        getUnreadComment: (state) => (commentId) => {
            return CommonHelper.findByKey(state.unreadComments, 'id', commentId);
        },
        getUnreadCommentsForProp: (state, getters) => (propName, propValue) => {
            var result = [];

            if (propValue) {
                for (let i = state.unreadComments.length - 1; i >= 0; i--) {
                    if (
                        state.unreadComments[i][propName] == propValue ||
                        // search in meta data as fallback
                        (
                            state.unreadComments[i].metaData &&
                            state.unreadComments[i].metaData[propName] == propValue
                        )
                    ) {
                        result.unshift(state.unreadComments[i]);
                    }
                }
            }

            return result;
        },
        getUnreadCommentsForScreen: (state, getters) => (screenId) => {
            return getters.getUnreadCommentsForProp('screenId', screenId);
        },
        getUnreadCommentsForPrototype: (state, getters) => (prototypeId) => {
            return getters.getUnreadCommentsForProp('prototypeId', prototypeId);
        },
        getUnreadCommentsForProject: (state, getters) => (projectId) => {
            return getters.getUnreadCommentsForProp('projectId', projectId);
        },
        getUnreadCommentsForComment: (state, getters) => (commentId) => {
            return getters.getUnreadCommentsForProp('replyTo', commentId);
        },
        isCommentUnread: (state) => (commentId) => {
            for (let i = state.unreadComments.length - 1; i >= 0; i--) {
                if (
                    state.unreadComments[i].id == commentId ||
                    state.unreadComments[i].replyTo == commentId
                ) {
                    return true;
                }
            }

            return false;
        },
    },
});
