<?php
namespace api\controllers;

use Yii;
use common\components\web\CUploadedFile;
use common\components\data\CActiveDataProvider;
use api\models\ScreenForm;

/**
 * Screens API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensController extends ApiController
{
    /**
     * @api {GET} /screens
     * 01. List screens
     * @apiName index
     * @apiGroup Screens
     * @apiDescription
     * Return list with screens from all projects owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * [
     *   {
     *     "id": 157,
     *     "versionId": 21,
     *     "title": "attachment2",
     *     "hotspots": {
     *       "hotspot_1490302776820": {
     *         "left": 504,
     *         "top": 204,
     *         "width": 130,
     *         "height": 106,
     *         "link": 161,
     *         "transition": "fade"
     *        }
     *     },
     *     "order": 1,
     *     "alignment": 2,
     *     "background": null,
     *     "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg",
     *     "createdAt": 1489926572,
     *     "updatedAt": 1489926572,
     *     "thumbs": {
     *       "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg",
     *       "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg"
     *     }
     *   },
     *   {
     *     "id": 158,
     *     "versionId": 21,
     *     "title": "attachment",
     *     "hotspots": null,
     *     "order": 2,
     *     "alignment": 1,
     *     "background": null,
     *     "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64.png",
     *     "createdAt": 1489926573,
     *     "updatedAt": 1489926573,
     *     "thumbs": {
     *       "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_medium.png",
     *       "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment_1489926573_64_thumb_small.png"
     *     }
     *   }
     * ]
     *
     * @apiUse 401
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        return new CActiveDataProvider([
            'query' => $user->getScreens(),
        ]);
    }

    /**
     * @api {POST} /screens
     * 02. Upload screen
     * @apiName create
     * @apiGroup Screens
     * @apiDescription
     * Create and return a new `Screen` model.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {File}   image                 Uploaded file
     * @apiParam {Number} versionId             Version id (must be from a project owned by the authenticated user)
     * @apiParam {String} title                 Screen title
     * @apiParam {Number} alignment             Screen alignment
     * @apiParam {String} [background]          Screen background HEX color code (eg. `#ffffff`)
     * @apiParam {Mixed}  [hotspots]            Screen hotspots as json encoded string or array in the following format: `{"hostpot_id_1": {...}, "hostpot_id_2": {...}}`
     * @apiParam {Number} hotspots.left         Left (X) hotspot coordinate
     * @apiParam {Number} hotspots.top          Left (Y) hotspot coordinate
     * @apiParam {Number} hotspots.width]       Hotspot width
     * @apiParam {Number} hotspots.height       Hotspot height
     * @apiParam {Mixed}  hotspots.link         Hotspot link target - screen id or external url
     * @apiParam {String} [hotspots.transition] Hotspot transition effect (`none`, `fade`, `slide-left`, `slide-right`, `slide-top`, `slide-bottom`)
     * @apiParam {Number} [order]               Screen position within its version
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 161,
     *   "versionId": 21,
     *   "title": "dashboard3",
     *   "hotspots": {
     *     "hotspot_1490302776820": {
     *       "left": 504,
     *       "top": 204,
     *       "width": 130,
     *       "height": 106,
     *       "link": 161,
     *       "transition": "none"
     *      }
     *   },
     *   "order": 3,
     *   "alignment": 1,
     *   "background": null,
     *   "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png",
     *   "createdAt": 1489927288,
     *   "updatedAt": 1489927288,
     *   "thumbs": {
     *     "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png",
     *     "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png"
     *   }
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": [
     *     "title": "Invalid screen ID."
     *   ]
     * }
     *
     * @apiUse 401
     */
    public function actionCreate()
    {
        $user  = Yii::$app->user->identity;
        $model = new ScreenForm($user, ['scenario' => ScreenForm::SCENARIO_CREATE]);
        $model->image = CUploadedFile::getInstanceByName('image');

        if (
            $model->load(Yii::$app->request->post(), '') &&
            ($screen = $model->save())
        ) {
            return $screen;
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {PUT} /screens/:id
     * 04. Update screen
     * @apiName update
     * @apiGroup Screens
     * @apiDescription
     * Update and return an existing `Screen` model from a project owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id                    Id of the screen to update (`GET` parameter)
     * @apiParam {Number} versionId             Version id (must be from a project owned by the authenticated user)
     * @apiParam {String} title                 Screen title
     * @apiParam {Number} alignment             Screen alignment
     * @apiParam {String} [background]          Screen background HEX color code (eg. `#ffffff`)
     * @apiParam {Mixed}  [hotspots]            Screen hotspots as json encoded string or array in the following format: `{"hostpot_id_1": {...}, "hostpot_id_2": {...}}`
     * @apiParam {Number} hotspots.left         Left (X) hotspot coordinate
     * @apiParam {Number} hotspots.top          Left (Y) hotspot coordinate
     * @apiParam {Number} hotspots.width]       Hotspot width
     * @apiParam {Number} hotspots.height       Hotspot height
     * @apiParam {Mixed}  hotspots.link         Hotspot link target - screen id or external url
     * @apiParam {String} [hotspots.transition] Hotspot transition effect (`none`, `fade`, `slide-left`, `slide-right`, `slide-top`, `slide-bottom`)
     * @apiParam {Number} [order]               Screen position within its version
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 161,
     *   "versionId": 21,
     *   "title": "New title",
     *   "hotspots": null,
     *   "order": 3,
     *   "alignment": 1,
     *   "background": null,
     *   "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png",
     *   "createdAt": 1489927288,
     *   "updatedAt": 1489927288,
     *   "thumbs": {
     *     "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png",
     *     "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png"
     *   }
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": [
     *     "title": "Title cannot be blank.",
     *     "versionId": "Invalid version ID."
     *   ]
     * }
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionUpdate($id)
    {
        $user   = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);

        if ($screen) {
            $model = new ScreenForm($user, ['scenario' => ScreenForm::SCENARIO_UPDATE]);

            if (
                $model->load(Yii::$app->request->bodyParams, '') &&
                $model->save($screen)
            ) {
                return $screen;
            }

            return $this->setErrorResponse(
                Yii::t('app', 'Oops, an error occurred while processing your request.'),
                $model->getFirstErrors()
            );
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {GET} /screens/:id
     * 03. View screen
     * @apiName viewzscdsa
     * @apiGroup Screens
     * @apiDescription
     * Return an existing `Screen` model from a project owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id Screen id
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 161,
     *   "versionId": 21,
     *   "title": "dashboard3",
     *   "hotspots": {
     *     "hotspot_1490302776820": {
     *       "left": 504,
     *       "top": 204,
     *       "width": 130,
     *       "height": 106,
     *       "link": 161
     *      }
     *   },
     *   "order": 3,
     *   "alignment": 1,
     *   "background": '#ffffff',
     *   "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3.png",
     *   "createdAt": 1489927288,
     *   "updatedAt": 1489927288,
     *   "thumbs": {
     *     "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_medium.png",
     *     "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/dashboard3_1489927288_3_thumb_small.png"
     *   }
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": [
     *     "title": "Title cannot be blank.",
     *     "versionId": "Invalid version ID."
     *   ]
     * }
     *
     * @apiUse 401
     */
    public function actionView($id)
    {
        $user    = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);

        if ($screen) {
            return $screen;
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {DELETE} /screens/:id
     * 05. Delete screen
     * @apiName delete
     * @apiGroup Screens
     * @apiDescription
     * Delete an existing `Screen` model from a project owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String} id Screen id
     *
     * @apiUse 204
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionDelete($id)
    {
        $user   = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);

        if ($screen && $screen->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->setNotFoundResponse();
    }
}
