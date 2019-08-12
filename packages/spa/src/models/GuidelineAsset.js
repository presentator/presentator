import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

const IMAGE_EXTENSIONS = ['png', 'jpg', 'jpeg', 'svg', 'bmp'];

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class GuidelineAsset extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.guidelineSectionId = !CommonHelper.isEmpty(data.guidelineSectionId) ? data.guidelineSectionId : null;
        this.type               = !CommonHelper.isEmpty(data.type)               ? data.type                : 'file';
        this.order              = !CommonHelper.isEmpty(data.order)              ? data.order               : 1;
        this.hex                = !CommonHelper.isEmpty(data.hex)                ? data.hex                 : '';
        this.title              = !CommonHelper.isEmpty(data.title)              ? data.title               : '';
        this.file               = !CommonHelper.isEmpty(data.file)               ? data.file                : {};
    }

    /**
     * Checks whether the current asset type is a file.
     *
     * @return {Boolean}
     */
    get isFile() {
        return this.type === 'file';
    }

    /**
     * Checks whether the current asset type is a color.
     *
     * @return {Boolean}
     */
    get isColor() {
        return this.type === 'color';
    }

    /**
     * Returns '#ffffff' or '#000000' based on current asset hex color.
     *
     * @return {String}
     */
    get contrastHex() {
        if (!this.isColor) {
            return '';
        }

        return CommonHelper.getContrastHex(this.hex);
    }

    /**
     * Returns the rgb equivalent of the current asset hex color.
     *
     * @return {String}
     */
    get rgb() {
        var rgbColors = CommonHelper.hexToRgb(this.hex);

        return 'rgb(' + rgbColors.r + ', ' + rgbColors.g + ', ' + rgbColors.b + ')';
    }

    /**
     * Returns current asset file extension (if is file type).
     *
     * @return {String}
     */
    get fileExtension() {
        if (this.isFile && this.file.original) {
            return this.file.original.split('.').pop();
        }

        return '';
    }

    /**
     * Checks whether the current asset type is a file.
     *
     * @return {Boolean}
     */
    get isImage() {
        return (
            this.isFile &&
            this.file.original &&
            IMAGE_EXTENSIONS.indexOf(this.fileExtension) >= 0
        );
    }

    /**
     * Returns single asset file url by its option key name (default to 'original').
     *
     * @param  {String} [option]
     * @return {String}
     */
    getFileUrl(option = 'original') {
        return this.file[option] || this.file['original'] || '';
    }
}
