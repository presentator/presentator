<template>
    <popup ref="popup">
        <template v-slot:header>
            <h4 class="title">{{ $t('Manage project admins') }}</h4>
        </template>
        <template v-slot:content>
            <div v-if="isLoadingLinkedUsers" class="block txt-center txt-hint">
                <span class="loader"></span>
            </div>
            <div v-if="!isLoadingLinkedUsers && !linkedUsers.length" class="block txt-center txt-hint">
                {{ $t('No users found.') }}
            </div>
            <div v-if="!isLoadingLinkedUsers && linkedUsers.length" class="users-list">
                <div v-for="user in linkedUsers"
                    :key="'admin_' + user.id"
                    class="user-list-item"
                    :class="{'active': loggedUser.id == user.id}"
                >
                    <figure class="avatar">
                        <img v-if="user.getAvatar('small')"
                            :src="user.getAvatar('small')"
                            alt="User avatar"
                        >
                        <i v-else class="fe fe-user"></i>
                    </figure>
                    <div class="content">
                        <span v-if="user.fullName" class="txt m-r-5">
                            {{ user.fullName }}
                            (<a :href="'mailto:' + user.email" class="link-hint">{{ user.email }}</a>)
                        </span>
                        <span v-else class="txt m-r-5">{{ user.email }}</span>
                        <span v-if="loggedUser.id == user.id" class="label label-transp-primary">{{ $t('You') }}</span>
                    </div>

                    <div class="list-ctrls">
                        <div class="list-ctrl-item ctrl-danger" @click.prevent="unlinkUser(user.id)"><i class="fe fe-trash"></i></div>
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:footer>
            <div class="form-group"
                tabindex="-1"
                :class="{'active': showSearchDropdown && searchTerm.length >= 2}"
                @focusout="onSearchFocusOut"
                @focusin="onSearchFocusIn"
            >
                <input type="text"
                    :placeholder="$t('Search for users by their email address or name...')"
                    autocomplete="prevent_autocomplete_popover"
                    v-model.trim="searchTerm"
                    @input="onSearchInputChange"
                >
                <div class="dropdown dropdown-compact input-dropdown">
                    <div v-if="isLoadingSuggestions" class="dropdown-item txt-hint"><span class="loader"></span></div>

                    <div v-if="!isLoadingSuggestions && !suggestedUsers.length" class="dropdown-item placeholder">
                        {{ $t('No users found.') }}
                    </div>

                    <template v-if="!isLoadingSuggestions && suggestedUsers.length">
                        <template v-for="(user, i) in suggestedUsers">
                            <div class="dropdown-item" @click.prevent="linkUser(user.id)">
                                {{ user.fullName ? (user.fullName + ' (' + user.email + ')') : user.email }}
                            </div>
                            <hr v-if="i+1 != suggestedUsers.length">
                        </template>
                    </template>
                </div>
            </div>
        </template>
    </popup>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Popup        from '@/components/Popup';
import User         from '@/models/User';

export default {
    name: 'admins-popup',
    components: {
        'popup': Popup,
    },
    props: {
        projectId: {
            required: true,
        }
    },
    data() {
        return {
            isLoadingLinkedUsers: false,
            isLoadingSuggestions: false,
            linkedUsers:          [],
            suggestedUsers:       [],
            searchTerm:           '',
            showSearchDropdown:   false,
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    methods: {
        open() {
            this.$refs.popup.open();

            this.resetSearch();

            this.loadLinkedUsers();

            this.$emit('open');
        },
        close() {
            this.$refs.popup.close();

            this.$emit('close');
        },
        resetSearch() {
            this.showSearchDropdown = false;
            this.searchTerm = '';
        },
        onSearchFocusIn(e) {
            this.showSearchDropdown = true;
        },
        onSearchFocusOut(e) {
            this.showSearchDropdown = false;
        },
        onSearchInputChange(e) {
            if (this.searchTerm.length >= 2) {
                this.showSearchDropdown = true;
                this.searchUsers();
            } else {
                this.showSearchDropdown = false;
            }
        },
        searchUsers() {
            this.isLoadingSuggestions = true;

            ApiClient.Projects.searchUsers(
                this.projectId,
                this.searchTerm
            ).then((response) => {
                this.suggestedUsers = User.createInstances(response.data);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingSuggestions = false;
            });
        },
        loadLinkedUsers() {
            if (this.isLoadingLinkedUsers) {
                return;
            }

            this.isLoadingLinkedUsers = true;

            ApiClient.Projects.getUsersList(this.projectId).then((response) => {
                this.linkedUsers = User.createInstances(response.data || []);

                // prepend the current user in the linked users list
                CommonHelper.unshiftByKey(this.linkedUsers, 'id', this.loggedUser.id);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingLinkedUsers = false;
            });
        },
        linkUser(userId) {
            // optimistic linking
            var user = CommonHelper.findByKey(this.suggestedUsers, userId);
            if (user) {
                this.$toast(this.$t('Successfully linked user "{user}".', {user: user.identifier}));

                this.linkedUsers.push(user);
            }
            this.showSearchDropdown = false;
            this.searchTerm = '';

            // actual linking
            ApiClient.Projects.linkUser(this.projectId, userId);
        },
        unlinkUser(userId) {
            if (this.loggedUser.id == userId) {
                let promptEmail = window.prompt(this.$t('Please type your email address to confirm unlinking your profile from the project:'));

                if (promptEmail !== this.$store.state.user.email) {
                    if (promptEmail) {
                        this.$toast(this.$t('Unlinking canceled - the provided email address does not match.'), 'warning');
                    }

                    return;
                }
            } else if (!window.confirm(this.$t('Do you really want to unlink the selected user?'))) {
                return;
            }

            // actual unlinking
            ApiClient.Projects.unlinkUser(this.projectId, userId);

            // optimistic unlinking
            var user = CommonHelper.findByKey(this.linkedUsers, 'id', userId);
            this.$toast(this.$t('Successfully unlinked user "{user}".', {user: user ? user.identifier : userId}));
            CommonHelper.removeByKey(this.linkedUsers, 'id', userId);

            // redirect on self unlinking
            if (this.loggedUser.id == userId) {
                this.$router.replace({ name: 'home' });
            }
        },
    },
}
</script>
