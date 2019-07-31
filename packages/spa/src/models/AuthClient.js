import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class AuthClient extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.name    = !CommonHelper.isEmpty(data.name)    ? data.name    : '';
        this.title   = !CommonHelper.isEmpty(data.title)   ? data.title   : '';
        this.state   = !CommonHelper.isEmpty(data.state)   ? data.state   : '';
        this.authUrl = !CommonHelper.isEmpty(data.authUrl) ? data.authUrl : '';
    }
}
