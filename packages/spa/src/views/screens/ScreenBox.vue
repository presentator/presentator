<template>
    <div class="box box-card box-screen"
        :class="{'active': isScreenSelected }"
        @mouseleave="$refs.screenDropdown ? $refs.screenDropdown.hide() : true"
    >
        <figure class="box-thumb"
            @click.ctrl.exact.stop.prevent="toggleScreenSelection(screen.id)"
        >
            <div class="crop-wrapper">
                <img v-if="screen.getImage('medium')"
                    :src="screen.getImage('medium')"
                    :alt="screen.title"
                    class="img"
                >
                <i v-else class="fe fe-image img"></i>
            </div>

            <div class="thumb-overlay">
                <router-link :to="{name: 'screen', params: {prototypeId: screen.prototypeId, screenId: screen.id}}" class="overlay-ctrl"></router-link>

                <router-link :to="{name: 'screen', params: {prototypeId: screen.prototypeId, screenId: screen.id}}"
                    class="box-ctrl handle center"
                >
                    <i class="fe fe-eye"></i>
                </router-link>

                <div class="box-ctrl check top-left">
                    <div class="form-group">
                        <input type="checkbox" :id="'bulk_check_screen_' + screen.id" v-model="isScreenSelected">
                        <label :for="'bulk_check_screen_' + screen.id"></label>
                    </div>
                </div>

                <div class="box-ctrl handle top-right">
                    <i class="fe fe-more-horizontal"></i>

                    <toggler ref="screenDropdown" class="dropdown dropdown-sm">
                        <div class="dropdown-item link-danger" @click.prevent="deleteScreen()">
                            <i class="fe fe-trash"></i>
                            <span class="txt">{{ $t('Delete') }}</span>
                        </div>
                    </toggler>
                </div>
            </div>
        </figure>

        <div class="box-content">
            <router-link :to="{name: 'screen', params: {prototypeId: screen.prototypeId, screenId: screen.id}}"
                class="title"
            >
                {{ screen.title }}
            </router-link>

            <div class="meta">
                <div class="meta-item">{{ $t('Uploaded {date}', {date: screen.createdAtFromNow}) }}</div>
                <div v-if="screenUnreadComments.length"
                    class="meta-item txt-danger"
                    :title="$t('Unread comments')"
                >
                    <i class="fe fe-message-circle"></i>
                    <span class="txt">{{ screenUnreadComments.length }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions, mapGetters } from 'vuex';
import ApiClient from '@/utils/ApiClient';
import Screen    from '@/models/Screen';

export default {
    name: 'screen-box',
    props: {
        screen: {
            type:     Screen,
            required: true,
        }
    },
    computed: {
        ...mapGetters({
            getUnreadCommentsForScreen: 'notifications/getUnreadCommentsForScreen',
        }),

        screenUnreadComments() {
            return this.getUnreadCommentsForScreen(this.screen.id);
        },
        isScreenSelected: {
            get() {
                return this.$store.getters['screens/isScreenSelected'](this.screen.id);
            },
            set(value) {
                if (value) {
                    this.selectScreen(this.screen.id);
                } else {
                    this.deselectScreen(this.screen.id);
                }
            },
        },
    },
    methods: {
        ...mapActions({
            selectScreen:          'screens/selectScreen',
            deselectScreen:        'screens/deselectScreen',
            toggleScreenSelection: 'screens/toggleScreenSelection',
        }),

        deleteScreen() {
            if (
                !this.screen.id ||
                !window.confirm(this.$t('Do you really want to delete screen "{title}"?', {title: this.screen.title}))
            ) {
                return;
            }

            // actual deletion
            ApiClient.Screens.delete(this.screen.id);

            // optimistic deletion
            this.$toast(this.$t('Successfully deleted screen "{title}".', {title: this.screen.title}));
            this.deselectScreen(this.screen.id);
            this.$emit('screenDelete', this.screen.id);
        },
    },
}
</script>
