<template>
    <div>
        <div class="flex-block">
            <figure class="avatar avatar-lg">
                <img v-if="user.getAvatar('small')" :src="user.getAvatar('small')" alt="User avatar">
                <i v-else class="fe fe-user"></i>
            </figure>
            <button type="button"
                ref="uploadContainer"
                class="btn btn-light-border btn-sm m-l-small btn-loader dz-clickable"
                :class="{'btn-loader-active': avatarProcessing}"
            >
                <span class="txt">{{ $t('Change avatar') }}</span>
            </button>
            <small v-if="user.getAvatar('small')"
                class="txt-danger link-fade m-l-small"
                @click.prevent="deleteAvatar()"
                v-tooltip.right="$t('Delete')"
            >
                <i class="fe fe-trash"></i>
            </small>
        </div>

        <div class="clearfix m-b-base"></div>

        <form @submit.prevent="saveChanges()">
            <div class="row">
                <div class="col-lg-6">
                    <form-field name="firstName">
                        <label for="user_first_name">{{ $t('First name') }}</label>
                        <input type="text" v-model="firstName" id="user_first_name">
                    </form-field>
                </div>
                <div class="col-lg-6">
                    <form-field name="lastName">
                        <label for="user_last_name">{{ $t('Last name') }}</label>
                        <input type="text" v-model="lastName" id="user_last_name">
                    </form-field>
                </div>
            </div>

            <div v-if="loggedUser.isSuperUser" class="row">
                <div class="col-6">
                    <form-field class="required" name="type">
                        <label for="user_type">{{ $t('Type') }}</label>
                        <select id="user_type" v-model="type" required>
                            <option value="regular">{{ $t('Regular') }}</option>
                            <option value="super">{{ $t('Super user') }}</option>
                        </select>
                    </form-field>
                </div>
                <div class="col-6">
                    <form-field class="required" name="status">
                        <label for="user_status">{{ $t('Status') }}</label>
                        <select id="user_status" v-model="status" required>
                            <option value="inactive">{{ $t('Inactive') }}</option>
                            <option value="active">{{ $t('Active') }}</option>
                        </select>
                    </form-field>
                </div>
            </div>

            <div v-if="changeEmail" class="row">
                <div class="col-lg-6">
                    <form-field v-if="loggedUser.isSuperUser" name="email">
                        <label for="user_email">{{ $t('Email') }}</label>
                        <input type="email" v-model="email" id="user_email" required>
                    </form-field>

                    <div v-else class="form-group">
                        <label for="user_email">{{ $t('Email') }}</label>
                        <div class="input-group">
                            <input type="text" v-model="user.email" id="user_email" disabled>
                            <div class="input-addon link-default bg-border" @click.prevent="openEmailChangePopup()">{{ $t('Change email') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6"></div>
            </div>

            <div class="flex-block">
                <button class="btn btn-primary btn-cons-lg btn-loader" :class="{'btn-loader-active': formProcessing}">
                    <span class="txt">{{ $t('Save changes') }}</span>
                </button>
                <router-link v-show="!formProcessing" :to="{name: 'home'}" class="link-hint m-l-small">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </router-link>
            </div>
        </form>

        <user-email-change-popup v-if="!loggedUser.isSuperUser" ref="emailChangePopup" :user="user"></user-email-change-popup>
    </div>
</template>

<script>
import { mapState }         from 'vuex';
import Dropzone             from 'dropzone';
import ApiClient            from '@/utils/ApiClient';
import CommonHelper         from '@/utils/CommonHelper';
import UserEmailChangePopup from '@/views/users/UserEmailChangePopup';
import User                 from '@/models/User';

export default {
    name: 'user-profile-form',
    props: {
        user: {
            type:     User,
            required: true,
        },
        changeEmail: {
            type:    Boolean,
            default: true,
        },
    },
    components: {
        'user-email-change-popup': UserEmailChangePopup,
    },
    data() {
        return {
            dropzone:              null,
            firstName:             '',
            lastName:              '',
            type:                  'regular',
            status:                'active',
            email:                 '',
            newEmail:              '',
            formProcessing:        false,
            avatarProcessing:      false,
            changeEmailProcessing: false,
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),
    },
    watch: {
        'user.id': function (newVal, oldVal) {
            this.loadForm();
        }
    },
    mounted() {
        this.loadForm()

        this.initAvatarUpload();
    },
    destroyed() {
        if (this.dropzone) {
            this.dropzone.destroy();
        }
    },
    methods: {
        // profile form
        loadForm() {
            this.firstName = this.user.firstName;
            this.lastName  = this.user.lastName;
            this.email     = this.user.email;
            this.type      = this.user.type;
            this.status    = this.user.status;
        },
        refreshUser(userData) {
            if (CommonHelper.isEmpty(userData)) {
                return;
            }

            this.user.load(userData);

            this.loadForm();

            // update the global logged in user data model if
            // the modified user is the current logged in
            if (this.user.id == this.loggedUser.id) {
                this.$store.dispatch('user/set', userData);
            }
        },
        saveChanges(callback) {
            if (this.formProcessing) {
                return;
            }

            this.formProcessing = true;

            ApiClient.Users.update(this.user.id, {
                firstName: this.firstName,
                lastName:  this.lastName,
                email:     this.email,
                type:      this.type,
                status:    this.status,
            }).then((response) => {
                this.$toast(this.$t('Successfully saved changes.'));

                this.refreshUser(response.data);

                if (CommonHelper.isFunction(callback)) {
                    callback(response);
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.formProcessing = false;
            });
        },

        // avatar
        deleteAvatar(callback) {
            if (
                this.avatarProcessing ||
                !window.confirm(this.$t('Do you really want to delete the avatar?'))
            ) {
                return;
            }

            this.avatarProcessing = true;

            ApiClient.Users.update(this.user.id, {
                deleteAvatar: true
            }).then((response) => {
                this.$toast(this.$t('Successfully deleted avatar.'));

                this.refreshUser(response.data);

                if (CommonHelper.isFunction(callback)) {
                    callback(response);
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.avatarProcessing = false;
            });
        },
        initAvatarUpload() {
            Dropzone.autoDiscover = false;

            this.dropzone = new Dropzone(this.$refs.uploadContainer, {
                url: ApiClient.$baseUrl + '/users/' + this.user.id,
                method: 'put',
                maxFiles: 1,
                timeout: 0,
                paramName: 'avatar',
                parallelUploads: 1,
                uploadMultiple: false,
                thumbnailWidth: null,
                thumbnailHeight: null,
                addRemoveLinks: false,
                createImageThumbnails: false,
                acceptedFiles: '.png, .jpg, .jpeg, .svg',
                previewTemplate: '<div style="display: none"></div>',
            });

            this.dropzone.on('addedfile', (file) => {
                // update the authorization header each time when a new file is selected
                this.dropzone.options.headers = Object.assign(this.dropzone.options.headers || {}, {
                    'Authorization': ('Bearer ' + ApiClient.$token),
                });
            });

            this.dropzone.on('sending', (file, xhr, formData) => {
                this.avatarProcessing = true;
            });

            this.dropzone.on('error', (file, response, xhr) => {
                var message = CommonHelper.getNestedVal(response, 'errors.avatar', this.$t('An error occurred while updating the user avatar.'));

                this.$toast(message, 'danger');
            });

            this.dropzone.on('success', (file, response) => {
                this.refreshUser(response);

                this.$toast(this.$t('Successfully updated user avatar.'));
            });

            this.dropzone.on('complete', (file) => {
                this.avatarProcessing = false;

                this.dropzone.removeFile(file); // reset maxFiles
            });
        },

        // change email for regular user
        openEmailChangePopup() {
            if (this.$refs.emailChangePopup) {
                this.$refs.emailChangePopup.open();
            }
        },
    },
}
</script>
