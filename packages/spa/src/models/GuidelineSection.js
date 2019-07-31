import BaseModel      from './BaseModel';
import GuidelineAsset from './GuidelineAsset';
import CommonHelper   from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class GuidelineSection extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.projectId   = !CommonHelper.isEmpty(data.projectId)   ? data.projectId   : null;
        this.order       = !CommonHelper.isEmpty(data.order)       ? data.order << 0  : 1;
        this.title       = !CommonHelper.isEmpty(data.title)       ? data.title       : '';
        this.description = !CommonHelper.isEmpty(data.description) ? data.description : '';

        if (CommonHelper.isArray(data.assets)) {
            this.assets = GuidelineAsset.createInstances(data.assets);
        } else {
            this.assets = this.assets || [];
        }
    }
}
