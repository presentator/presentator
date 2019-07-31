import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class HotspotTemplate extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.prototypeId = !CommonHelper.isEmpty(data.prototypeId) ? data.prototypeId : null;
        this.title       = !CommonHelper.isEmpty(data.title)       ? data.title       : '';
        this.screenIds   = CommonHelper.isArray(data.screenIds)    ? data.screenIds   : [];
    }
}
