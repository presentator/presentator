import CommonHelper from '@/utils/CommonHelper';

/**
 * BaseModel class intended to be inherited by all API models.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class BaseModel {
    /**
     * @param {Object} [data]
     */
    constructor(data) {
        this.load(data);
    }

    /**
     * Loads and normalize model properties.
     *
     * @param {Object} [data]
     */
    load(data) {
        data = data || {};

        this.id        = !CommonHelper.isEmpty(data.id)        ? data.id        : null;
        this.createdAt = !CommonHelper.isEmpty(data.createdAt) ? data.createdAt : null;
        this.updatedAt = !CommonHelper.isEmpty(data.updatedAt) ? data.updatedAt : null;
        this.metaData  = CommonHelper.isObject(data.metaData)  ? data.metaData  : (this.metaData || {});
    }

    /**
     * Exports all model properties as a new plain object.
     *
     * @return {Object}
     */
    export() {
        return Object.assign({}, this);
    }

    /**
     * Returns new model instances by cloning the current model data.
     *
     * @param  {Object} [data] Additional data to load into the created model.
     * @return {Object}
     */
    clone(data) {
        data = data || {};

        return new this.constructor(Object.assign(this.export(), data));
    }

    /**
     * Returns human readable string for the `createdAt` datetime string value.
     *
     * @return {String}
     */
    get createdAtFromNow() {
        return CommonHelper.getTimeFromNow(this.createdAt);
    }

    /**
     * Returns human readable string for the `updatedAt` datetime string value.
     *
     * @return {String}
     */
    get updatedAtFromNow() {
        return CommonHelper.getTimeFromNow(this.updatedAt);
    }

    /**
     * Returns new model instances from data items array.
     *
     * @param  {Array} items
     * @return {Array}
     */
    static createInstances(items) {
        var result = [];

        items = items || [];

        for (let i in items) {
            result.push(new this(items[i]));
        }

        return result;
    }
}
