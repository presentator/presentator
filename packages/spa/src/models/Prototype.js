import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

const SIZES_LIST = {
    '375x812':   {'label': 'iPhone X (375x812)', 'width': 375, 'height': 812},
    '375x667':   {'label': 'iPhone 6/7/8 (375x667)', 'width': 375, 'height': 667},
    '1024x1366': {'label': 'iPad Pro (1024x1366)', 'width': 1024, 'height': 1366},
    '768x1024':  {'label': 'iPad Mini/Air (768x1024)', 'width': 768, 'height': 1024},
    '324x394':   {'label': 'Apple Watch (324x394)', 'width': 324, 'height': 394},
    '412x824':   {'label': 'Pixel 3 (412x824)', 'width': 412, 'height': 824},
    '360x740':   {'label': 'Samsung Galaxy S9 (360x740)', 'width': 360, 'height': 740},
    '540x720':   {'label': 'Surface Duo (540x720)', 'width': 540, 'height': 720},
    'other':     {'label': 'Other device', 'width': 0, 'height': 0},
};

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Prototype extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.projectId   = !CommonHelper.isEmpty(data.projectId)   ? data.projectId   : null;
        this.title       = !CommonHelper.isEmpty(data.title)       ? data.title       : '';
        this.type        = !CommonHelper.isEmpty(data.type)        ? data.type        : 'desktop';
        this.width       = !CommonHelper.isEmpty(data.width)       ? data.width       : 0;
        this.height      = !CommonHelper.isEmpty(data.height)      ? data.height      : 0;
        this.scaleFactor = !CommonHelper.isEmpty(data.scaleFactor) ? data.scaleFactor : 1;
    }

    /**
     * Checks whether the prototype's type is for desktop.
     *
     * @return {Boolean}
     */
    get isForDesktop() {
        return this.type == 'desktop';
    }

    /**
     * Checks whether the prototype's type is for mobile.
     *
     * @return {Boolean}
     */
    get isForMobile() {
        return this.type == 'mobile';
    }

    get sizeKey() {
        var key = this.width + 'x' + this.height;

        return SIZES_LIST[key] ? key : 'other';
    }

    get sizeOptions() {
        return SIZES_LIST[this.sizeKey];
    }

    /**
     * Returns list with predefined device sizes.
     *
     * @return {Object}
     */
    static getSizesList() {
        return SIZES_LIST;
    }
}
