<template>
    <div class="notifications-wrapper">
        <transition-group name="notifications-list" class="notifications-list" tag="div">
            <div
                v-for="(message, i) in messages"
                :key="message.text"
                class="notification"
                :class="['notification-' + message.type, {'is-repeated': message.isRepeated}]"
            >
                <span class="close-handle" v-if="message.closeBtn" @click.prevent="removeMessage(message.text)"></span>

                <div class="content">{{ message.text }}</div>
            </div>
        </transition-group>
    </div>
</template>

<script>
import { mapState, mapActions } from 'vuex';

export default {
    name: 'toast',
    computed: {
        ...mapState({
            messages: state => state.toast.messages,
        }),
    },
    methods: {
        ...mapActions({
            removeMessage: 'toast/removeMessage',
        }),
    },
}
</script>
