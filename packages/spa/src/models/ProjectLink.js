import BaseModel    from './BaseModel';
import Prototype    from './Prototype';
import Project      from './Project';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class ProjectLink extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.projectId         = !CommonHelper.isEmpty(data.projectId) ? data.projectId : null;
        this.slug              = !CommonHelper.isEmpty(data.slug)      ? data.slug      : '';
        this.passwordProtected = data. passwordProtected               ? true           : false;
        this.allowComments     = data.allowComments                    ? true           : false;
        this.allowGuideline    = data.allowGuideline                   ? true           : false;

        if (CommonHelper.isArray(data.prototypes)) {
            this.prototypes = Prototype.createInstances(data.prototypes);
        } else {
            this.prototypes = this.prototypes || [];
        }

        this.project = null;
        if (!CommonHelper.isEmpty(data.projectInfo)) {
            // restricted project info
            this.project = new Project(data.projectInfo);
        } else if (data.project && data.project instanceof Project) {
            // full project model
            this.project = data.project;
        }
    }

    /**
     * @return {String}
     */
    get baseUrl() {
        var url = CommonHelper.getNestedVal(window, 'location.origin', '');

        if (CommonHelper.getNestedVal(window, 'location.href', '').indexOf('/#/') > 0) {
            // keeps app "hash" routing mode
            url = url + '/#';
        }

        // trim trailing slash
        url = url.endsWith('/') ? url.substring(0, url.length - 1) : url;

        return url;
    }

    /**
     * @return {String}
     */
    get relativeUrl() {
        return '/' + this.slug;
    }

    /**
     * @return {String}
     */
    get fullUrl() {
        return this.baseUrl + this.relativeUrl;
    }

    /**
     * Returns IDs array of the prototypes that this project link is restricted to.
     *
     * @return {Array}
     */
    get prototypeIds() {
        var result = [];

        for (let i in this.prototypes) {
            if (this.prototypes[i].id) {
                result.push(this.prototypes[i].id);
            }
        }

        return result;
    }
}
