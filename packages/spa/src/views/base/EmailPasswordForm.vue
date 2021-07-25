<template>
    <form class="login-form" @submit.prevent="onSubmit">
        <alert class="alert-transp-danger m-b-base" :closeTimeout="0" :visibility="showFormError">
            {{ $t('Invalid login credentials.') }}
        </alert>

        <form-field class="form-group-lg" name="email">
            <div class="input-group">
                <label for="sign_in_email" class="input-addon p-r-0">
                    <i class="fe fe-mail"></i>
                </label>
                <input type="email" v-model="email" id="sign_in_email" :placeholder="$t('Email')" required>
            </div>
        </form-field>

        <form-field class="form-group-lg" name="password">
            <div class="input-group">
                <label for="sign_in_password" class="input-addon p-r-0">
                    <i class="fe fe-lock"></i>
                </label>
                <input type="password" v-model="password" id="sign_in_password" :placeholder="$t('Password')" required>
            </div>
            <router-link :to="{name: 'forgotten-password'}" class="forgotten-password-link link-primary">
                {{ $t('Forgotten password?') }}
            </router-link>
        </form-field>

        <button class="btn btn-primary btn-lg btn-loader block" :class="{'btn-loader-active': isProcessing}">
            <span class="txt">{{ $t('Sign in') }}</span>
            <i class="fe fe-arrow-right-circle"></i>
        </button>
    </form>
</template>

<script>
import ApiClient from '@/utils/ApiClient';

export default {
    name: 'email-password-form',
    data() {
        return {
            email:         '',
            password:      '',
            showFormError: false,
            isProcessing:  false,
        }
    },
    methods: {
        onSubmit() {
            if (this.isProcessing) {
                return;
            }

            this.showFormError = false;
            this.isProcessing  = true;

            ApiClient.Users.login(
                this.email,
                this.password
            ).then((response) => {
                this.$loginByResponse(response);
            }).catch((err) => {
                this.showFormError = true;
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
