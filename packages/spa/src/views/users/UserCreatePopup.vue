<template>
    <form @submit.prevent="submitForm()">
        <popup class="popup-sm" ref="popup"
            :closeOnEsc="!isProcessing"
            :closeOnOverlay="!isProcessing"
            :closeBtn="!isProcessing"
        >
            <template v-slot:header>
                <h4 class="title">{{ $t('New user') }}</h4>
            </template>

            <template v-slot:content>
                <form-field class="required" name="email">
                    <label for="user_email">{{ $t('Email') }}</label>
                    <input type="email" v-model.trim="email" id="user_email" required>
                </form-field>

                <div class="row">
                    <div class="col-6">
                        <form-field name="firstName">
                            <label for="user_first_name">{{ $t('First name') }}</label>
                            <input type="text" v-model.trim="firstName" id="user_first_name">
                        </form-field>
                    </div>
                    <div class="col-6">
                        <form-field name="lastName">
                            <label for="user_last_name">{{ $t('Last name') }}</label>
                            <input type="text" v-model.trim="lastName" id="user_last_name">
                        </form-field>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <form-field class="required" name="password">
                            <label for="user_password">{{ $t('Password') }}</label>
                            <input type="password" id="user_password" v-model.trim="password" required>
                        </form-field>
                    </div>
                    <div class="col-6">
                        <form-field class="required" name="passwordConfirm">
                            <label for="user_password_confirm">{{ $t('Password confirm') }}</label>
                            <input type="password" id="user_password_confirm" v-model.trim="passwordConfirm" required>
                        </form-field>
                    </div>
                </div>

                <div class="row">
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
            </template>

            <template v-slot:footer>
                <button v-show="!isProcessing" type="button" class="btn btn-light-border" @click.prevent="close()">
                    <span class="txt">{{ $t('Cancel') }}</span>
                </button>
                <div class="flex-fill-block"></div>
                <button type="submit" class="btn btn-primary btn-cons btn-loader" :class="{'btn-loader-active': isProcessing}">
                    <span class="txt">{{ $t('Create user') }}</span>
                </button>
            </template>
        </popup>
    </form>
</template>

<script>
import ApiClient    from '@/utils/ApiClient';
import CommonHelper from '@/utils/CommonHelper';
import Popup        from '@/components/Popup';

const defaultFormData = {
    email:           '',
    firstName:       '',
    lastName:        '',
    type:            'regular',
    status:          'active',
    password:        '',
    passwordConfirm: '',
};

export default {
    name: 'user-create-popup',
    components: {
        'popup': Popup,
    },
    data() {
        return Object.assign({}, defaultFormData, {
            isProcessing: false,
        });
    },
    methods: {
        open() {
            this.isProcessing = false;

            this.resetForm();

            this.$refs.popup.open();
        },
        close() {
            this.isProcessing = false;

            this.$refs.popup.close();
        },
        resetForm() {
            for (let key in defaultFormData) {
                this[key] = defaultFormData[key];
            }
        },
        submitForm() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;

            ApiClient.Users.create({
                email:           this.email,
                firstName:       this.firstName,
                lastName:        this.lastName,
                type:            this.type,
                status:          this.status,
                password:        this.password,
                passwordConfirm: this.passwordConfirm,
            }).then((response) => {
                var userId = CommonHelper.getNestedVal(response, 'data.id');

                if (userId) {
                    this.$router.push({
                        name:   'users-edit',
                        params: { userId: userId },
                    });
                }
            }).catch((err) => {
                this.$errResponseHandler(err);
            }).finally(() => {
                this.isProcessing = false;
            });
        },
    },
}
</script>
