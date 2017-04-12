<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\models\Screen;
use common\models\ScreenComment;
use common\models\ProjectPreview;
use common\models\ScreenCommentForm;

/**
 * ScreenComments controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsController extends AppController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'][] = [
            'actions' => ['ajax-create', 'ajax-get-comments'],
            'allow' => true,
        ];

        $behaviors['verbs']['actions'] = [
            'ajax-delete' => ['post'],
            'ajax-create' => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Deletes screen comment via ajax.
     *
     * NB! Requires the following post parameters:
     * `id` - ID of the comment model
     *
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxDelete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $comment = $user->findScreenCommentById(Yii::$app->request->post('id', -1));

        if ($comment && $comment->delete()) {
            return [
                'success' => true,
                'message' => Yii::t('app', 'Successfully deleted comment.'),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Creates new comment target via ajax.
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxCreate()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $previewSlug = $request->get('previewSlug', '');

        $model = null;
        $showDelete = false;
        if ($previewSlug) {
            $preview = ProjectPreview::findOneBySlug($previewSlug, ProjectPreview::TYPE_VIEW_AND_COMMENT);
            if ($preview) {
                $model = new ScreenCommentForm($preview->project, ['scenario' => ScreenCommentForm::SCENARIO_PREVIEW]);
            }
        } elseif (!Yii::$app->user->isGuest) {
            $showDelete = true;
            $model = new ScreenCommentForm(Yii::$app->user->identity, ['scenario' => ScreenCommentForm::SCENARIO_USER]);
        }

        if ($model) {
            if (
                $model->load($request->post(), '') &&
                ($comment = $model->save())
            ) {
                $commentsListHtml = $this->getCommentsListHtml($comment, $showDelete);

                return [
                    'success'          => true,
                    'comment'          => $comment,
                    'commentsListHtml' => $commentsListHtml,
                    'message'          => Yii::t('app', 'Successfully created new comment target.'),
                ];
            }

            return [
                'success' => false,
                'message' => implode('<br/>', $model->getFirstErrors()),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Returns primary comment + replies list by primary comment id via ajax.
     * @return array
     */
    public function actionAjaxGetComments()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $previewSlug = $request->get('previewSlug', '');
        $commentId   = $request->get('commentId', -1);

        $primaryComment = null;
        $showDelete = false;
        if ($previewSlug) {
            $preview = ProjectPreview::findOneBySlug($previewSlug, ProjectPreview::TYPE_VIEW_AND_COMMENT);
            if ($preview) {
                $primaryComment = $preview->project->findScreenCommentById($commentId);
            }
        } elseif (!Yii::$app->user->isGuest) {
            $showDelete = true;
            $primaryComment = Yii::$app->user->identity->findScreenCommentById($commentId);
        }

        if ($primaryComment && !$primaryComment->replyTo) {
            $commentsListHtml = $this->getCommentsListHtml($primaryComment, $showDelete);

            if (!$previewSlug && !Yii::$app->user->isGuest) {
                $primaryComment->markAsRead(Yii::$app->user->identity);
                foreach ($primaryComment->replies as $reply) {
                    $reply->markAsRead(Yii::$app->user->identity);
                }
            }

            return [
                'success'          => true,
                'commentsListHtml' => $commentsListHtml,
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Helper to get the content of _comments_list partial.
     * @param  ScreenComment $primaryComment
     * @param  boolean       $showDelete Whether to show the delete handle or not.
     * @return string
     */
    protected function getCommentsListHtml(ScreenComment $primaryComment, $showDelete = true)
    {
        if ($primaryComment->replyTo) {
            // is not primary comment
            $primaryComment = $primaryComment->primaryComment;
        }

        $comments = $primaryComment->getReplies()->with('fromUser')->all();
        array_unshift($comments, $primaryComment);

        return $this->renderPartial('_comments_list', ['comments' => $comments, 'showDelete' => $showDelete]);
    }
}
