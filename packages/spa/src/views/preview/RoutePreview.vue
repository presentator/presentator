<template>
    <div v-if="!isSubRouteActive" class="full-page-flex">
        <div class="flex-fill-block"></div>

        <div v-if="isMainLoaderActive" class="block txt-center">
            <span class="loader loader-lg loader-blend"></span>
        </div>

        <div v-if="isAuthorizeFormActive" class="container-wrapper container-wrapper-sm">
            <div class="panel m-t-10 m-b-base">
                <div class="panel-content">
                    <h3 class="panel-title">{{ $t('This link is password protected') }}</h3>

                    <p class="txt-center">{{ $t('Enter the password below to view the project:') }}</p>

                    <form @submit.prevent="authorize()">
                        <form-field class="form-group-lg" name="password">
                            <div class="input-group">
                                <label for="project_link_password" class="input-addon p-r-0">
                                    <i class="fe fe-lock"></i>
                                </label>
                                <input type="password" v-model="password" id="project_link_password" :placeholder="$t('Password')" required>
                            </div>
                        </form-field>

                        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isAuthorizing}">
                            <span class="txt">View project</span>
                            <i class="fe fe-arrow-right-circle"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="flex-fill-block"></div>
    </div>

    <keep-alive v-else :max="3">
        <router-view
            :project="project"
            :collaborators="collaborators"
            :projectLink="projectLink"
        ></router-view>
    </keep-alive>
</template>

<script>
import { mapState, mapActions, mapGetters } from 'vuex';
import ApiClient     from '@/utils/ApiClient';
import CommonHelper  from '@/utils/CommonHelper';
import Project       from '@/models/Project';
import ProjectLink   from '@/models/ProjectLink';

export default {
    name: 'project-link-preview',
    props: {
        slug: {
            type:     String,
            required: true,
            default:  '',
        }
    },
    data() {
        return {
            isMainLoaderActive:    true,
            isAuthorizeFormActive: false,
            isSubRouteActive:      false,
            isLoadingInitialData:  false,
            isAuthorizing:         false,
            password:              '',
            project:               new Project,
            projectLink:           new ProjectLink,
            collaborators:         [],
        }
    },
    computed: {
        ...mapState({
            activePrototypeId: state => state.prototypes.activePrototypeId,
            prototypes:        state => state.prototypes.prototypes,
            previewToken:      state => state.preview.previewToken,
        }),
        ...mapGetters({
            getPrototype:    'prototypes/getPrototype',
            activePrototype: 'prototypes/activePrototype',
        }),
    },
    watch: {
        previewToken(newVal, oldVal) {
            if (!newVal && !this.isMainLoaderActive) {
                this.showAuthorizeForm();
            }
        }
    },
    mounted() {
        this.loadLocalPreviewToken(this.slug);

        this.showMainLoader();

        if (this.previewToken) {
            this.loadInitialData();
        } else {
            this.authorize(this.slug, this.password, false);
        }
    },
    methods: {
        ...mapActions({
            setActivePrototypeId:  'prototypes/setActivePrototypeId',
            setPrototypes:         'prototypes/setPrototypes',
            setPreviewToken:       'preview/setPreviewToken',
            loadLocalPreviewToken: 'preview/loadLocalPreviewToken',
            clearPreviewToken:     'preview/clearPreviewToken',
        }),

        showMainLoader() {
            this.isMainLoaderActive    = true;
            this.isAuthorizeFormActive = false;
            this.isSubRouteActive      = false;
        },
        showAuthorizeForm() {
            this.resetAuthorizeForm();

            this.isMainLoaderActive    = false;
            this.isAuthorizeFormActive = true;
            this.isSubRouteActive      = false;
        },
        showSubroute() {
            this.isMainLoaderActive    = false;
            this.isAuthorizeFormActive = false;
            this.isSubRouteActive      = true;
        },
        resetAuthorizeForm() {
            this.password = '';
        },
        authorize(slug, password, handleErrors = true) {
            slug     = slug     || this.slug     || '';
            password = password || this.password || '';

            if (this.isAuthorizing) {
                return;
            }

            this.isAuthorizing = true;

            ApiClient.Previews.authorize(slug, password).then((response) => {
                this.resetAuthorizeForm();

                this.setPreviewToken(CommonHelper.getNestedVal(response, 'data.token'));

                this.setInitialData(response.data);
            }).catch((err) => {
                if (CommonHelper.getNestedVal(err, 'response.status', 400) == 401) {
                    this.showAuthorizeForm();

                    if (handleErrors) {
                        this.$errResponseHandler(err);
                    }
                } else {
                    this.$errResponseHandler(err);
                }
            }).finally(() => {
                this.isAuthorizing = false;
            });
        },
        loadInitialData(callback) {
            if (this.isLoadingInitialData) {
                return;
            }

            this.isLoadingInitialData = true;

            ApiClient.Previews.getOne(this.previewToken).then((response) => {
                this.setInitialData(response.data);

                if (CommonHelper.isFunction(callback)) {
                    callback(response);
                }
            }).catch((err) => {
                if (CommonHelper.getNestedVal(err, 'response.status', 400) == 401) {
                    this.showAuthorizeForm();
                }
            }).finally(() => {
                this.isLoadingInitialData = false;
            });
        },
        setInitialData(data) {
            data = data || {};

            var routePrototypeId = this.$route.params.prototypeId;

            this.project.load(data.project);

            this.projectLink.load(data.projectLink);

            this.collaborators = data.collaborators || [];

            this.$setDocumentTitle(this.project.title);

            this.setPrototypes(data.prototypes);

            // set active prototype
            if (this.getPrototype(routePrototypeId)) {
                this.setActivePrototypeId(routePrototypeId);
            } else if (this.prototypes.length) {
                this.setActivePrototypeId(this.prototypes[this.prototypes.length - 1].id);
            } else if (this.projectLink.allowGuideline) {
                // switch to guideline mode if is allowed and no prototypes are available
                this.$router.replace({
                    name:   'preview-guideline',
                    params: {slug: this.projectLink.slug},
                });
            }

            this.showSubroute();
        },
    },
}
</script>
