import BaseModel    from './BaseModel';
import User         from './User';
import CommonHelper from '@/utils/CommonHelper';

/**
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
export default class ScreenComment extends BaseModel {
    /**
     * {@inheritdoc}
     */
    load(data) {
        data = data || {};


        super.load(data);

        this.id       = !CommonHelper.isEmpty(data.id)       ? data.id                 : -1;
        this.replyTo  = !CommonHelper.isEmpty(data.replyTo)  ? data.replyTo            : null;
        this.screenId = !CommonHelper.isEmpty(data.screenId) ? data.screenId           : null;
        this.from     = !CommonHelper.isEmpty(data.from)     ? data.from               : '';
        this.message  = !CommonHelper.isEmpty(data.message)  ? data.message            : '';
        this.left     = !CommonHelper.isEmpty(data.left)     ? data.left               : 0;
        this.top      = !CommonHelper.isEmpty(data.top)      ? data.top                : 0;
        this.status   = !CommonHelper.isEmpty(data.status)   ? data.status             : 'pending';
        this.user     = !CommonHelper.isEmpty(data.fromUser) ? new User(data.fromUser) : null;

        // data is probably another ScreenComment instance
        if (!this.user && data.user instanceof User) {
            this.user = data.user;
        }
    }

    /**
     * Checks whether the comment is a new (not saved yet) record.
     *
     * @return {Boolean}
     */
    get isNew() {
        return this.id == -1;
    }

    /**
     * Checks whether the comment is in a pending state.
     *
     * @return {Boolean}
     */
    get isPending() {
        return this.status == 'pending';
    }

    /**
     * Checks whether the comment is in a resolved state.
     *
     * @return {Boolean}
     */
    get isResolved() {
        return this.status == 'resolved';
    }

    /**
     * Checks whether the comment is a is a reply one.
     *
     * @return {Boolean}
     */
    get isReply() {
        return this.replyTo > 0;
    }

    /**
     * Checks whether the comment is a primary/root one.
     *
     * @return {Boolean}
     */
    get isPrimary() {
        return !this.isReply;
    }
}
