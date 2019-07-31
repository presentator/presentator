<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\ScreenComment;
use presentator\api\models\forms\ScreenCommentSearch;
use presentator\api\models\forms\ScreenCommentForm;
use presentator\api\data\ArrayDataProvider;

/**
 * ScreenComments rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsController extends ApiController
{
    /**
     * Returns paginated list with screen comments.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new ScreenCommentSearch($user->findScreenCommentsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `ScreenComment` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new ScreenCommentForm($user, null, [
            'scenario' => ScreenCommentForm::SCENARIO_CREATE,
        ]);

        $model->load(Yii::$app->request->post());

        if ($comment = $model->save()) {
            return $comment->toArray([], ['fromUser']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `ScreenComment` model data.
     *
     * @param  integer $id ID of the comment to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $comment = $user->findScreenCommentById($id);
        if (!$comment) {
            throw new NotFoundHttpException();
        }

        $model = new ScreenCommentForm($user, $comment, [
            'scenario' => ScreenCommentForm::SCENARIO_UPDATE,
        ]);

        $model->load(Yii::$app->request->post());

        if ($comment = $model->save()) {
            return $comment->toArray([], ['fromUser']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `ScreenComment` model for detailed view.
     *
     * @param  integer $id ID of the comment to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $comment = $user->findScreenCommentById($id);
        if (!$comment) {
            throw new NotFoundHttpException();
        }

        return $comment->toArray([], ['fromUser']);
    }

    /**
     * Deletes an existing `ScreenComment` model by its id.
     *
     * @param  integer $id ID of the comment to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $comment = $user->findScreenCommentById($id);
        if (!$comment) {
            throw new NotFoundHttpException();
        }

        if ($comment->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Returns array with all unread screen comments related to the authenticated user.
     *
     * @return mixed
     */
    public function actionListUnread()
    {
        $user = Yii::$app->user->identity;

        $comments = $user->findUnreadScreenComments();

        ScreenComment::eagerLoad($comments, ['fromUser', 'screen.prototype.project']);

        return new ArrayDataProvider([
            'allModels'  => $comments,
            'pagination' => false,
            'expand'     => ['fromUser', 'metaData'],
        ]);
    }

    /**
     * Marks a single comment as read for the authenticated in user (if notification was sent).
     *
     * @param  integer $id ID of the comment to mark.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionRead($id)
    {
        $user = Yii::$app->user->identity;

        $comment = $user->findScreenCommentById($id);
        if (!$comment) {
            throw new NotFoundHttpException();
        }

        if ($comment->markAsReadForUser($user)) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }
}
