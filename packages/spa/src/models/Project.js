import BaseModel    from './BaseModel';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class Project extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};

        super.load(data);

        this.title          = !CommonHelper.isEmpty(data.title)          ? data.title          : '';
        this.archived       = !CommonHelper.isEmpty(data.archived)       ? data.archived << 0  : 0;
        this.pinned         = !CommonHelper.isEmpty(data.pinned)         ? data.pinned << 0    : 0;
        this.featuredScreen = !CommonHelper.isEmpty(data.featuredScreen) ? data.featuredScreen : {};
    }

    /**
     * Returns project featured screen thumb url by its size (default to 'original').
     *
     * @param  {String} size
     * @return {String}
     */
    getFeaturedScreen(size = 'original') {
        return this.featuredScreen[size] || this.featuredScreen['original'] || '';
    }

    /**
     * Checks whether the project is archived.
     *
     * @return {Boolean}
     */
    get isArchived() {
        return this.archived ? true : false;
    }

    /**
     * Checks whether the project is pinned.
     *
     * @return {Boolean}
     */
    get isPinned() {
        return this.pinned ? true : false;
    }
}
