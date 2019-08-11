<template>
    <form @submit.prevent="upsert()">
        <popup ref="popup">
            <template v-slot:header>
                <span class="side-ctrl side-ctrl-left"
                    v-tooltip.right="$t('Back')"
                    @click.prevent="close()"
                >
                    <i class="fe fe-arrow-left"></i>
                </span>

                <h4 class="title">
                    <template v-if="isUpdate">{{ $t('Edit project link') }}</template>
                    <template v-else>{{ $t('Create project link') }}</template>
                </h4>
            </template>
            <template v-slot:content>
                <div v-if="isUpdate" class="alert alert-light-border txt-center m-b-20">
                    <strong>{{ link.fullUrl }}</strong>
                </div>

                <form-field class="form-group-switch" name="allowComments">
                    <input type="checkbox" v-model="allowComments" id="link_upsert_allow_comments">
                    <label for="link_upsert_allow_comments">{{ $t('Allow comments') }}</label>
                </form-field>

                <form-field class="form-group-switch" name="allowGuideline">
                    <input type="checkbox" v-model="allowGuideline" id="link_upsert_allow_style_guide">
                    <label for="link_upsert_allow_style_guide">{{ $t('Guideline') }}</label>
                </form-field>

                <form-field class="form-group-switch m-b-small" name="passwordProtected">
                    <input type="checkbox" v-model="passwordProtected" id="link_upsert_password_protected">
                    <label for="link_upsert_password_protected">{{ $t('Protect with password') }}</label>
                    <small v-if="isUpdate && link.passwordProtected && passwordProtected" class="link-hint" @click="changePassword = !changePassword">
                        ({{ $t('Change password') }} <i class="v-align-middle fe" :class="changePassword ? 'fe-chevron-up' : 'fe-chevron-down'"></i>)
                    </small>
                </form-field>
                <form-field v-if="passwordProtected && (!link.passwordProtected || changePassword)" class="m-b-small" name="password">
                    <label for="link_upsert_password">{{ $t('Password') }}</label>
                    <input type="password" v-model="password" id="link_upsert_password" required>
                </form-field>
                <div class="clearfix m-b-small"></div>

                <div class="form-group form-group-switch m-b-0">
                    <input type="checkbox" v-model="restrictPrototypes" id="link_upsert_restrict_prototypes">
                    <label for="link_upsert_restrict_prototypes">{{ $t('Restrict access to prototypes') }}</label>
                </div>
                <div v-if="restrictPrototypes" class="form-group-section m-t-20">
                    <span v-if="isLoadingPrototypes" class="loader"></span>

                    <div v-if="!isLoadingPrototypes && !prototypes.length" class="block txt-hint">
                        {{ $t('No prototypes found.') }}
                    </div>

                    <template v-if="!isLoadingPrototypes && prototypes.length">
                        <div v-for="prototype in prototypes"
                            :key="prototype.id"
                            class="form-group m-b-10"
                        >
                            <input type="checkbox" :id="'prototype_chekbox_' + prototype.id" :value="prototype.id" v-model="selectedPrototypes">
                            <label :for="'prototype_chekbox_' + prototype.id">{{ prototype.title }}</label>
                        </div>
                    </template>
                </div>
            </template>
            <template v-slot:footer>
                <button type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Back') }}</span>
                </button>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ isUpdate ? $t('Update') : $t('Create') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Popup        from '@/components/Popup';
import ProjectLink  from '@/models/ProjectLink';
import Prototype    from '@/models/Prototype';

export default {
    name: 'upsert-link-popup',
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
            link:                new ProjectLink,
            isLoadingPrototypes: false,
            isProcessing:        false,
            allowComments:       true,
            allowGuideline:      true,
            passwordProtected:   false,
            changePassword:      false,
            password:            null,
            prototypes:          [],
            selectedPrototypes:  [],
            restrictPrototypes:  false,
        }
    },
    computed: {
        isUpdate() {
            return this.link.id > 0;
        }
    },
    watch: {
        passwordProtected(newVal, oldVal) {
            this.changePassword = false;

            if (!newVal) {
                this.password = '';
            } else {
                this.password = null;
            }
        },
    },
    methods: {
        resetForm() {
            this.isLoadingPrototypes = false;
            this.isProcessing        = false;
            this.allowComments       = this.link.id ? this.link.allowComments   : true;
            this.allowGuideline      = this.link.id ? this.link.allowGuideline : true;
            this.password            = null;
            this.changePassword      = false;
            this.passwordProtected   = this.link.passwordProtected;
            this.prototypes          = [];
            this.selectedPrototypes  = this.link.prototypeIds;
            this.restrictPrototypes  = this.selectedPrototypes.length ? true : false;
        },
        open(linkData) {
            if (!CommonHelper.isEmpty(linkData)) {
                this.link.load(linkData);
            } else {
                this.link = new ProjectLink;
            }

            this.loadPrototypes();

            this.resetForm();

            this.$refs.popup.open();

            this.$emit('open');
        },
        close() {
            this.$refs.popup.close();

            this.$emit('close');
        },
        upsert() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            if (this.link.id) {
                this.update();
            } else {
                this.create();
            }
        },
        create() {
            ApiClient.ProjectLinks.create({
                projectId:      this.projectId,
                allowComments:  this.allowComments,
                allowGuideline: this.allowGuideline,
                password:       this.password,
                prototypes:     this.restrictPrototypes ? this.selectedPrototypes : [],
            }).then((response) => {
                this.$toast(this.$t('Successfully created project link.'));

                this.resetForm();

                this.close();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        update(link) {
            link = link || this.link;

            var password = null;
            if (!this.passwordProtected) {
                password = '';
            } else if (!link.passwordProtected || this.changePassword) {
                password = this.password;
            }

            ApiClient.ProjectLinks.update(link.id, {
                allowComments:  this.allowComments,
                allowGuideline: this.allowGuideline,
                password:       password,
                prototypes:     this.restrictPrototypes ? this.selectedPrototypes : [],
            }).then((response) => {
                this.$toast(this.$t('Successfully updated project link.'));

                this.resetForm();

                this.close();
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
        loadPrototypes() {
            this.isLoadingPrototypes = true;

            ApiClient.Prototypes.getList(1, 100, {
                'search[projectId]': this.projectId,
            }).then((response) => {
                this.prototypes = Prototype.createInstances(response ? response.data : []);
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingPrototypes = false;
            });
        },
    },
}
</script>
