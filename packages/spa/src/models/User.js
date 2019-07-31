import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class User extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.email     = !CommonHelper.isEmpty(data.email)     ? data.email       : '';
        this.firstName = !CommonHelper.isEmpty(data.firstName) ? data.firstName   : '';
        this.lastName  = !CommonHelper.isEmpty(data.lastName)  ? data.lastName    : '';
        this.type      = !CommonHelper.isEmpty(data.type)      ? data.type        : 'regular';
        this.status    = !CommonHelper.isEmpty(data.status)    ? data.status      : 'active';
        this.avatar    = !CommonHelper.isEmpty(data.avatar)    ? data.avatar      : {};
        this.settings  = !CommonHelper.isEmpty(data.settings)  ? data.settings    : {};
    }

    /**
     * Returns single user setting value by its name.
     *
     * @param  {String} name
     * @param  {Mixed} defaultValue
     * @return {Mixed}
     */
    getSetting(name, defaultValue) {
        return CommonHelper.getNestedVal(this.settings, name, defaultValue);
    }

    /**
     * Returns user avatar url by its size (default to 'original').
     *
     * @param  {String} [size]
     * @return {String}
     */
    getAvatar(size = 'original') {
        return this.avatar[size] || this.avatar['original'] || '';
    }

    /**
     * Checks whether the current model is a super user/admin.
     *
     * @return {Boolean}
     */
    get isSuperUser() {
        return this.type == 'super';
    }

    /**
     * Checks whether the current model is a regular user.
     *
     * @return {Boolean}
     */
    get isRegular() {
        return this.id > 0 && !this.isSuperUser;
    }

    get fullName() {
        return (this.firstName + ' ' + this.lastName).trim();
    }

    get identifier() {
        return this.fullName || this.email || ('ID ' + this.id);
    }
}
