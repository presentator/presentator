import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Hotspot extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.screenId          = !CommonHelper.isEmpty(data.screenId)          ? data.screenId          : null;
        this.hotspotTemplateId = !CommonHelper.isEmpty(data.hotspotTemplateId) ? data.hotspotTemplateId : null;
        this.type              = !CommonHelper.isEmpty(data.type)              ? data.type              : 'screen';
        this.left              = !CommonHelper.isEmpty(data.left)              ? data.left              : 0;
        this.top               = !CommonHelper.isEmpty(data.top)               ? data.top               : 0;
        this.width             = !CommonHelper.isEmpty(data.width)             ? data.width             : 0;
        this.height            = !CommonHelper.isEmpty(data.height)            ? data.height            : 0;
        this.settings          = CommonHelper.isObject(data.settings)          ? data.settings          : {};
    }
}
