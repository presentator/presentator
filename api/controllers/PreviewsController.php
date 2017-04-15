<?php
namespace api\controllers;

use Yii;
use common\models\ProjectPreview;
use common\models\ScreenCommentForm;

/**
 * Public projects preview API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewsController extends ApiController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        unset($behaviors['authenticator']); // all actions are public

        return $behaviors;
    }

    /**
     * @api {GET} /previews/:slug
     * 01. Get project preview
     * @apiName projectPreview
     * @apiGroup Previews
     * @apiDescription
     * Returns a ProjectPreview model with its related project.
     *
     * @apiParam {String} slug       ProjectPreview model slug
     * @apiParam {String} [password] Project password (if has any)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 18,
     *   "projectId": 9,
     *   "slug": "ckfaBI6X",
     *   "type": 2,
     *   "createdAt": 1489904385,
     *   "updatedAt": 1489904385,
     *   "project": {
     *     "id": 9,
     *     "title": "test",
     *     "type": 1,
     *     "subtype": null,
     *     "createdAt": 1489904385,
     *     "updatedAt": 1490285838,
     *     "featured": {
     *       "id": 157,
     *       "versionId": 21,
     *       "title": "attachment2",
     *       "hotspots": null,
     *       "order": 1,
     *       "alignment": 0,
     *       "background": null,
     *       "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg",
     *       "createdAt": 1489926572,
     *       "updatedAt": 1489926572,
     *       "thumbs": {
     *         "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg",
     *         "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg"
     *       }
     *     },
     *     "versions": [
     *       {
     *         "id": 21,
     *         "projectId": 9,
     *         "order": 1,
     *         "createdAt": 1489904385,
     *         "updatedAt": 1489904385,
     *         "screens": [
     *           {
     *             "id": 157,
     *             "versionId": 21,
     *             "title": "attachment2",
     *             "hotspots": null,
     *             "order": 1,
     *             "alignment": 0,
     *             "background": null,
     *             "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg",
     *             "createdAt": 1489926572,
     *             "updatedAt": 1489926572,
     *             "thumbs": {
     *               "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg",
     *               "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg"
     *             }
     *           },
     *           {
     *             "id": 158,
     *             "versionId": 21,
     *             "title": "attachment",
     *             "hotspots": null,
     *             "order": 2,
     *             "alignment": 0,
     *             "background": null,
     *             "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64.png",
     *             "createdAt": 1489926573,
     *             "updatedAt": 1489926573,
     *             "thumbs": {
     *               "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_medium.png",
     *               "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_small.png"
     *             }
     *           }
     *         ]
     *       }
     *     ]
     *   }
     * }
     *
     * @apiUse 404
     *
     * @apiErrorExample {json} 401 Unauthorized (example):
     * {
     *   "message": "The project is password protected.",
     *   "errors": []
     * }
     *
     * @apiErrorExample {json} 403 Forbidden (example):
     * {
     *   "message": "You must provide a valid project password.",
     *   "errors": []
     * }
     */
    public function actionView($slug, $password = '')
    {
        $result = $this->findPreview($slug, $password);
        if (!($result instanceof ProjectPreview)) {
            return $result; // output error response
        }

        return $result->toArray([], ['project.featured', 'project.versions.screens']);
    }

    /**
     * @api {POST} /previews/:slug
     * 02. Leave comment
     * @apiName leaveComment
     * @apiGroup Previews
     * @apiDescription
     * Leave a new comment to a specific project preview screen (the `slug` must relate to a `ProjectPreview` model with type *View and Comment*).
     * Returns the new created comment.
     *
     * @apiParam {String} slug       ProjectPreview model slug (`GET` parameter)
     * @apiParam {String} [password] Project password (`GET` parameter)
     * @apiParam {String} message    Comment message
     * @apiParam {String} from       Sender email address
     * @apiParam {Number} screenId   Id of the screen to leave the comment at (**optional** for a reply comment)
     * @apiParam {Number} posX       Left position of the comment target (**optional** for a reply comment)
     * @apiParam {Number} posY       Top position of the comment target (**optional** for a reply comment)
     * @apiParam {Number} [replyTo]  Id of the comment to reply
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 68
     *   "replyTo": null,
     *   "screenId": 157,
     *   "posX": 100,
     *   "posY": 145,
     *   "message": "Lorem ipsum dolor sit amet",
     *   "from": "test123@presentator.io",
     *   "createdAt": 1490289032,
     *   "updatedAt": 1490289032,
     * }
     *
     * @apiUse 404
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": [
     *     "screenId": "Invalid screen ID."
     *   ]
     * }
     *
     * @apiErrorExample {json} 401 Unauthorized (example):
     * {
     *   "message": "The project is password protected.",
     *   "errors": []
     * }
     *
     * @apiErrorExample {json} 403 Forbidden (example):
     * {
     *   "message": "You must provide a valid project password.",
     *   "errors": []
     * }
     */
    public function actionAddComment($slug, $password = '')
    {
        $result = $this->findPreview($slug, $password, ProjectPreview::TYPE_VIEW_AND_COMMENT);
        if (!($result instanceof ProjectPreview)) {
            return $result; // output error response
        }

        $model = new ScreenCommentForm($result->project, ['scenario' => ScreenCommentForm::SCENARIO_PREVIEW]);

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
     * Helper method to find a ProjectPreview model by its slug and project password (optional).
     * On sucess returns the ProjectPreview model, otherwise - error response array.
     * @param  string      $slug     Preview model slug.
     * @param  string      $password Project password (if has any)
     * @param  string|null $type     Preview type mode.
     * @return array|ProjectPreview
     */
    protected function findPreview($slug, $password = '', $type = null)
    {
        $preview = ProjectPreview::findOneBySlug($slug, $type);
        if (!$preview) {
            return $this->setNotFoundResponse();
        }

        if ($preview->project->isPasswordProtected()) {
            if (!$password) {
                return $this->setErrorResponse(Yii::t('app', 'The project is password protected.'), [], 401);
            } elseif (!$preview->project->validatePassword($password)) {
                return $this->setErrorResponse(Yii::t('app', 'You must provide a valid project password.'), [], 403);
            }
        }

        return $preview;
    }
}
