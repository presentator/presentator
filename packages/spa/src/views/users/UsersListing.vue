<template>
    <div>
        <div v-if="withFilterBar" class="search-bar">
            <div class="search-input-wrapper" :class="{'active': searchTerm.length > 0}">
                <span class="search-clear" v-tooltip.left="$t('Clear')" @click.prevent="resetFilters()"></span>
                <input type="input"
                    class="search-input"
                    :placeholder="$t('Search users')"
                    v-model.trim="searchTerm"
                    @input="onSearchInputChange"
                >
            </div>
        </div>

        <table class="table v-align-middle m-b-base">
            <thead>
                <tr>
                    <th class="min-width">ID</th>
                    <th>{{ $t('Profile') }}</th>
                    <th>{{ $t('Status') }}</th>
                    <th>{{ $t('Type') }}</th>
                    <th>{{ $t('Created') }}</th>
                    <th>{{ $t('Updated') }}</th>
                    <th class="min-width txt-right"></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users" :key="user.id">
                    <td class="min-width">{{ user.id }}</td>
                    <td>
                        <figure class="avatar v-align-middle m-r-10">
                            <img v-if="user.getAvatar('small')"
                                :src="user.getAvatar('small')"
                                alt="User avatar"
                            >
                            <i class="fe fe-user" v-else></i>
                        </figure>
                        <span class="txt v-align-middle m-r-5">{{ user.fullName || 'N/A' }} ({{ user.email }})</span>
                        <span v-if="loggedUser.id == user.id" class="label label-transp-primary v-align-middle">You</span>
                    </td>
                    <td>
                        <span v-if="user.status === 'active'" class="label label-transp-success">{{ $t('Active') }}</span>
                        <span v-else class="label label-transp-danger">{{ $t('Inactive') }}</span>
                    </td>
                    <td>
                        <span v-if="user.isSuperUser" class="label label-transp-warning">{{ $t('Super user') }}</span>
                        <span v-else class="label label-light-border">{{ $t('Regular user') }}</span>
                    </td>
                    <td :title="user.createdAtLocal">{{ user.createdAtFromNow }}</td>
                    <td :title="user.updatedAtLocal">{{ user.updatedAtFromNow }}</td>
                    <td class="min-width txt-right">
                        <router-link :to="{name: 'user', params: {userId: user.id}}" class="btn btn-sm btn-cons-sm btn-transp-primary">
                            <i class="fe fe-edit"></i>
                            <span class="txt">{{ $t('Edit') }}</span>
                        </router-link>
                    </td>
                </tr>

                <tr v-if="!users.length">
                    <td colspan="7" class="txt-center">
                        <span v-if="isLoadingUsers" class="loader txt-hint"></span>
                        <h6 v-else class="m-0">{{ $t('No users were found.') }}</h6>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="block txt-center">
            <button class="btn btn-warning btn-lg btn-cons-xl m-l-small m-r-small"
                @click.prevent="resetFilters()"
                v-show="hasActiveFilters"
            >
                <span class="txt">{{ $t('Reset filters') }}</span>
            </button>

            <button class="btn btn-transp-primary btn-lg btn-cons-xl btn-loader m-l-small m-r-small"
                :class="{'btn-loader-active': isLoadingUsers}"
                @click.prevent="loadUsers(currentPage + 1, false)"
                v-show="hasMoreUsers"
            >
                <span class="txt">{{ $t('Load more users') }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import { mapState } from 'vuex';
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import User         from '@/models/User';

const USERS_PER_PAGE  = 30;

export default {
    name: 'users-listing',
    props: {
        withFilterBar: {
            type:    Boolean,
            default: true,
        },
    },
    data() {
        return {
            isLoadingUsers: false,
            searchTerm:     '',
            totalUsers:     0,
            currentPage:    1,
            users:          [],
        }
    },
    computed: {
        ...mapState({
            loggedUser: state => state.user.user,
        }),

        hasMoreUsers() {
            return this.totalUsers > this.users.length;
        },
        hasActiveFilters() {
            return this.searchTerm.length > 0;
        },
    },
    mounted() {
        this.loadUsers();
    },
    methods: {
        resetFilters() {
            this.searchTerm = '';

            this.loadUsers();
        },
        resetList() {
            this.users       = [];
            this.totalUsers  = 0;
            this.currentPage = 1;
        },
        onSearchInputChange(e) {
            if (this.searchTimeoutId) {
                clearTimeout(this.searchTimeoutId);
            }

            if (!this.searchTerm.length) {
                this.loadUsers();
            } else {
                this.resetList();
                this.isLoadingUsers = true;

                // throttle
                this.searchTimeoutId = setTimeout(() => {
                    this.loadUsers();
                }, 250);
            }
        },
        loadUsers(page = 1, reset = true) {
            this.isLoadingUsers = true;

            if (reset) {
                this.resetList();
            }

            ApiClient.Users.getList(page, USERS_PER_PAGE, {
                'envelope':           true,
                'search[identifier]': (this.withFilterBar ? this.searchTerm : ''),
                'sort':               '-createdAt',
            }).then((response) => {
                var users = User.createInstances(CommonHelper.getNestedVal(response, 'data.response', []));

                for (let i in users) {
                    CommonHelper.pushUnique(this.users, users[i]);
                }

                this.totalUsers  = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-total-count', 0) << 0;
                this.currentPage = CommonHelper.getNestedVal(response, 'data.headers.x-pagination-current-page', 1) << 0;
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isLoadingUsers = false;
            });
        },
    }
}
</script>
