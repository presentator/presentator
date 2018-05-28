<?php
namespace api\controllers;

use Yii;
use common\components\data\CActiveDataProvider;
use api\models\VersionForm;

/**
 * Versions API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionsController extends ApiController
{
    /**
     * @api {GET} /versions
     * 01. List versions
     * @apiName index
     * @apiGroup Versions
     * @apiDescription
     * Return list with versions from all projects owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * [
     *   {
     *     "id": 19,
     *     "projectId": 7,
     *     "order": 1,
     *     "createdAt": 1489904382,
     *     "updatedAt": 1489904382
     *   },
     *   {
     *     "id": 20,
     *     "projectId": 8,
     *     "order": 1,
     *     "createdAt": 1489904384,
     *     "updatedAt": 1489904384
     *   }
     * ]
     *
     * @apiUse 401
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        return new CActiveDataProvider([
            'query' => $user->getVersions(),
        ]);
    }

    /**
     * @api {POST} /versions
     * 02. Create version
     * @apiName create
     * @apiGroup Versions
     * @apiDescription
     * Create and return a new `Version` model.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number}  projectId     Id of a project owned by the authenticated user
     * @apiParam {String}  title         Version title
     * @apiParam {Number}  type          Version type
     * @apiParam {Number}  [subtype]     Version subtype (**required** only for versions with type `2 - tablet` or `3 - mobile`)
     * @apiParam {Boolean} [autoScale]   Auto fit scale flag for mobile and tablet Version types (`false` by default)
     * @apiParam {Boolean} [retinaScale] Retina scale flag for desktop Version types (`false` by default)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 25
     *   "projectId": 7,
     *   "order": 2,
     *   "createdAt": 1490299034,
     *   "updatedAt": 1490299034,
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": {
     *     "projectId": "Invalid project ID."
     *   }
     * }
     *
     * @apiUse 401
     */
    public function actionCreate()
    {
        $user  = Yii::$app->user->identity;
        $model = new VersionForm($user, ['scenario' => VersionForm::SCENARIO_CREATE]);

        if (
            $model->load(Yii::$app->request->post(), '') &&
            ($version = $model->save())
        ) {
            return $version;
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {PUT} /versions/:id
     * 02. Update version
     * @apiName update
     * @apiGroup Versions
     * @apiDescription
     * Update and return an existing `Version` model owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number}  id            Version id
     * @apiParam {String}  title         Version title
     * @apiParam {Number}  type          Version type
     * @apiParam {Number}  [subtype]     Version subtype (**required** only for versions with type `2 - tablet` or `3 - mobile`)
     * @apiParam {Boolean} [autoScale]fit    Auto scale flag for mobile and tablet Version types (`false` by default)
     * @apiParam {Boolean} [retinaScale] Retina scale flag for desktop Version types (`false` by default)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 25
     *   "projectId": 7,
     *   "order": 2,
     *   "createdAt": 1490299034,
     *   "updatedAt": 1490299034,
     * }
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": {
     *     "type": "This field is required."
     *   }
     * }
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionUpdate($id)
    {
        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById($id);

        if (!$version) {
            return $this->setNotFoundResponse();
        }

        $model = new VersionForm($user, ['scenario' => VersionForm::SCENARIO_UPDATE]);

        if (
            $model->load(Yii::$app->request->bodyParams, '') &&
            ($updatedVersion = $model->save($version))
        ) {
            return $updatedVersion;
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {GET} /versions/:id
     * 03. View version
     * @apiName view
     * @apiGroup Versions
     * @apiDescription
     * Return an existing `Version` model owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id Version id
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 25
     *   "projectId": 7,
     *   "order": 2,
     *   "createdAt": 1490299034,
     *   "updatedAt": 1490299034,
     * }
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionView($id)
    {
        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById($id);

        if ($version) {
            return $version;
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {DELETE} /versions/:id
     * 04. Delete version
     * @apiName delete
     * @apiGroup Versions
     * @apiDescription
     * Delete an existing `Version` model from a project owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String} id Version id
     *
     * @apiUse 204
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionDelete($id)
    {
        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById($id);

        if ($version) {
            if ($version->isTheOnlyOne()) {
                return $this->setErrorResponse(
                    Yii::t('app', 'You can not delete the only one project version.'),
                    $version->getFirstErrors()
                );
            }

            if ($version->delete()) {
                Yii::$app->response->statusCode = 204;

                return null;
            }
        }

        return $this->setNotFoundResponse();
    }
}
