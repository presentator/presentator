import CommonHelper  from '@/utils/CommonHelper';
import ScreenComment from '@/models/ScreenComment';

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            comments: [],
        };
    },
    mutations: {
        setComments(state, commentsData) {
            state.comments = ScreenComment.createInstances(commentsData);
        },
        addComment(state, commentData) {
            CommonHelper.pushUnique(state.comments, new ScreenComment(commentData));
        },
        updateComment(state, commentData) {
            commentData = commentData || {};

            var comment = CommonHelper.findByKey(state.comments, 'id', commentData.id);

            if (comment) {
                let updatedComment = comment.clone(commentData);

                CommonHelper.removeByKey(state.comments, 'id', comment.id);

                state.comments.push(updatedComment);
            }
        },
        removeComment(state, id) {
            CommonHelper.removeByKey(state.comments, 'id', id);
        },
    },
    actions: {
        setComments(context, commentsData) {
            context.commit('setComments', commentsData);
        },
        appendComments(context, commentsData) {
            for (let i in commentsData) {
                context.dispatch('addComment', commentsData[i]);
            }
        },
        addComment(context, commentData) {
            context.commit('addComment', commentData);
        },
        updateComment(context, commentData) {
            context.commit('updateComment', commentData);
        },
        removeComment(context, id) {
            context.commit('removeComment', id);
        },
    },
    getters: {
        getComment: (state) => (id) => {
            return CommonHelper.findByKey(state.comments, 'id', id);
        },
        getCommentsForScreen: (state, getters) => (screenId, status = null, dateOrdered = true) => {
            var result = [];

            for (let i = state.comments.length - 1; i >= 0; i--) {
                if (
                    state.comments[i].screenId == screenId &&
                    (!status || state.comments[i].status == status)
                ) {
                    result.unshift(state.comments[i]);
                }
            }

            if (dateOrdered) {
                result.sort((a, b) => (Date.parse(a['createdAt']) - Date.parse(b['createdAt'])));
            }

            return result;
        },
    },
});
