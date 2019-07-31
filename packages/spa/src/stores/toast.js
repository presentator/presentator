import Vue          from 'vue';
import CommonHelper from '@/utils/CommonHelper';

const baseMessage = {
    'text':              '',
    'type':              '',
    'closeBtn':          true,
    'timeout':           4500,
    'isRepeated':        false,
    'repeatedTimeoutId': null,
    'removeTimeoutId':   null,
};

export default CommonHelper.createResettableStore({
    namespaced: true,
    initialState() {
        return {
            messages: [],
        }
    },
    mutations: {
        setMessages(state, messages) {
            Vue.set(state, 'messages', messages || {});
        },
        addMessage(state, message) {
            message = Object.assign({}, baseMessage, message);

            for (let i = state.messages.length - 1; i >= 0; i--) {
                // check for duplicated/repeated message
                if (state.messages[i].text === message.text) {
                    message.repeatedTimeoutId = state.messages[i].repeatedTimeoutId;
                    message.removeTimeoutId   = state.messages[i].removeTimeoutId;
                    message.isRepeated        = true;

                    state.messages.splice(i, 1);

                    break;
                }
            }

            state.messages.push(message);
        },
        removeMessage(state, messageText) {
            for (let i = state.messages.length - 1; i >= 0; i--) {
                if (state.messages[i].text === messageText) {
                    if (state.messages[i].removeTimeoutId) {
                        clearTimeout(state.messages[i].removeTimeoutId);
                    }

                    state.messages.splice(i, 1);
                    break;
                }
            }
        },
    },
    actions: {
        setMessages(context, messages) {
            context.commit('setMessages', messages);
        },
        clearMessages(context) {
            context.commit('setMessages', {});
        },
        addMessage(context, message) {
            context.commit('addMessage', message);

            let lastAddedMessage = context.state.messages[context.state.messages.length - 1];
            if (!lastAddedMessage) {
                return;
            }

            if (lastAddedMessage.isRepeated) {
                if (lastAddedMessage.repeatedTimeoutId) {
                    clearTimeout(lastAddedMessage.repeatedTimeoutId);
                }

                lastAddedMessage.repeatedTimeoutId = setTimeout(() => {
                    lastAddedMessage.isRepeated = false;
                }, 400); // duplicated/repeated message animation delay
            }

            if (lastAddedMessage.removeTimeoutId) {
                clearTimeout(lastAddedMessage.removeTimeoutId);
                lastAddedMessage.removeTimeoutId = null;
            }

            if (lastAddedMessage.timeout > 0) {
                lastAddedMessage.removeTimeoutId = setTimeout(() => {
                    context.dispatch('removeMessage', lastAddedMessage.text);
                }, lastAddedMessage.timeout);
            }
        },
        removeMessage(context, messageText) {
            context.commit('removeMessage', messageText);
        },
    },
});
