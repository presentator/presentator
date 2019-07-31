import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Screen extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data = {}) {
        data = data || {};

        super.load(data);

        this.prototypeId = !CommonHelper.isEmpty(data.prototypeId) ? data.prototypeId    : null;
        this.order       = !CommonHelper.isEmpty(data.order)       ? data.order << 0     : 1;
        this.title       = !CommonHelper.isEmpty(data.title)       ? data.title          : '';
        this.alignment   = !CommonHelper.isEmpty(data.alignment)   ? data.alignment      : 'center';
        this.background  = !CommonHelper.isEmpty(data.background)  ? data.background     : '#ffffff';
        this.fixedHeader = !CommonHelper.isEmpty(data.fixedHeader) ? data.fixedHeader    : 0;
        this.fixedFooter = !CommonHelper.isEmpty(data.fixedFooter) ? data.fixedFooter    : 0;
        this.file        = !CommonHelper.isEmpty(data.file)        ? data.file           : {};
    }

    /**
     * @return {Boolean}
     */
    get isLeftAligned() {
        return this.alignment === 'left';
    }

    /**
     * @return {Boolean}
     */
    get isCenterAligned() {
        return this.alignment === 'center';
    }

    /**
     * @return {Boolean}
     */
    get isRightAligned() {
        return this.alignment === 'right';
    }

    /**
     * Returns the current screen file extension based on its file name.
     *
     * @return {String}
     */
    get fileExtension() {
        if (this.file.original) {
            return this.file.original.split('.').pop();
        }

        return '';
    }

    /**
     * Returns model's image url by its size key (default to 'original').
     *
     * @param  {String} [size]
     * @return {String}
     */
    getImage(size = 'original') {
        return this.file[size] || this.file['original'] || '';
    }
}
