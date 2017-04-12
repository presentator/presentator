<?php
namespace api\controllers;

use Yii;
use common\components\data\CActiveDataProvider;
use common\models\ScreenCommentForm;

/**
 * ScreenComments API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreenCommentsController extends ApiController
{
    /**
     * @api {GET} /comments
     * 01. List comments
     * @apiName index
     * @apiGroup Screen comments
     * @apiDescription
     * Return list with comments from all screens owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * [
     *   {
     *     "id": 58,
     *     "replyTo": null,
     *     "screenId": 157,
     *     "from": "gani.georgiev@gmail.com",
     *     "message": "asdasd",
     *     "isRead": 0,
     *     "posX": 550,
     *     "posY": 235,
     *     "createdAt": 1489997571,
     *     "updatedAt": 1489997571
     *   },
     *   {
     *     "id": 59,
     *     "replyTo": 58,
     *     "screenId": 157,
     *     "from": "gani.georgiev@gmail.com",
     *     "message": "asdasd",
     *     "isRead": 0,
     *     "posX": 550,
     *     "posY": 235,
     *     "createdAt": 1489997660,
     *     "updatedAt": 1489997660
     *   },
     *   {
     *     "id": 68,
     *     "replyTo": null,
     *     "screenId": 159,
     *     "from": "test123@presentator.io",
     *     "message": "Lorem ipsum dolor sit amet",
     *     "isRead": 0,
     *     "posX": 100,
     *     "posY": 145,
     *     "createdAt": 1490289032,
     *     "updatedAt": 1490289032
     *   }
     * ]
     *
     * @apiUse 401
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        return new CActiveDataProvider([
            'query' => $user->getScreenComments(),
        ]);
    }

    /**
     * @api {POST} /comments
     * 02. Create comment
     * @apiName create
     * @apiGroup Screen comments
     * @apiDescription
     * Create and return a new `ScreenComment` model.
     * The related comment screen must be from a project owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String} message    Comment message
     * @apiParam {Number} [replyTo]  Id of the comment to reply
     * @apiParam {Number} screenId   Id of the screen to leave the comment at (**optional** for a reply comment)
     * @apiParam {Number} posX       Left position of the comment target (**optional** for a reply comment)
     * @apiParam {Number} posY       Top position of the comment target (**optional** for a reply comment)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 68,
     *   "replyTo": null,
     *   "screenId": 159,
     *   "from": "test123@presentator.io",
     *   "message": "Lorem ipsum dolor sit amet",
     *   "isRead": 0,
     *   "posX": 100,
     *   "posY": 145,
     *   "createdAt": 1490289032,
     *   "updatedAt": 1490289032
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": [
     *     "screenId": "Invalid screen ID."
     *   ]
     * }
     *
     * @apiUse 401
     */
    public function actionCreate()
    {
        $user  = Yii::$app->user->identity;
        $model = new ScreenCommentForm($user, ['scenario' => ScreenCommentForm::SCENARIO_USER]);

        if (
            $model->load(Yii::$app->request->post(), '') &&
            ($comment = $model->save())
        ) {
            return $comment;
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {GET} /comments/:id
     * 03. View comment
     * @apiName view
     * @apiGroup Screen comments
     * @apiDescription
     * Return an existing `ScreenComment` model from a screen owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id Comment id
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 68,
     *   "replyTo": null,
     *   "screenId": 159,
     *   "from": "test123@presentator.io",
     *   "message": "Lorem ipsum dolor sit amet",
     *   "isRead": 0,
     *   "posX": 100,
     *   "posY": 145,
     *   "createdAt": 1490289032,
     *   "updatedAt": 1490289032
     * }
     *
     * @apiUse 404
     */
    public function actionView($id)
    {
        $user    = Yii::$app->user->identity;
        $comment = $user->findScreenCommentById($id);

        if ($comment) {
            return $comment;
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {DELETE} /comments/:id
     * 04. Delete comment
     * @apiName delete
     * @apiGroup Screen comments
     * @apiDescription
     * Delete an existing `ScreenComment` model from a screen owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String} id Comment id
     *
     * @apiUse 204
     *
     * @apiUse 404
     */
    public function actionDelete($id)
    {
        $user   = Yii::$app->user->identity;
        $comment = $user->findScreenCommentById($id);

        if ($comment && $comment->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->setNotFoundResponse();
    }
}
