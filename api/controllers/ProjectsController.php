<?php
namespace api\controllers;

use Yii;
use common\components\data\CActiveDataProvider;
use api\models\ProjectForm;

/**
 * Projects API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectsController extends ApiController
{
    /**
     * @api {GET} /projects
     * 01. List projects
     * @apiName index
     * @apiGroup Projects
     * @apiDescription
     * Return list with projects owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * [
     *   {
     *     "id": 7,
     *     "title": "Test 1",
     *     "createdAt": 1489904382,
     *     "updatedAt": 1489904382,
     *     "featured": null,
     *     "previews": [
     *       {
     *         "id": 1,
     *         "projectId": 7,
     *         "slug": "preview-slug-1",
     *         "type": 1,
     *         "createdAt": 1524306495,
     *         "updatedAt": 1524306495
     *       },
     *       {
     *         "id": 2,
     *         "projectId": 7,
     *         "slug": "preview-slug-2",
     *         "type": 2,
     *         "createdAt": 1524306495,
     *         "updatedAt": 1524306495
     *       }
     *     ]
     *   },
     *   {
     *     "id": 9,
     *     "title": "Test 2",
     *     "createdAt": 1489904385,
     *     "updatedAt": 1490286679,
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
     *     "previews": [
     *       {
     *         "id": 1,
     *         "projectId": 9,
     *         "slug": "preview-slug-1",
     *         "type": 1,
     *         "createdAt": 1524306495,
     *         "updatedAt": 1524306495
     *       },
     *       {
     *         "id": 2,
     *         "projectId": 9,
     *         "slug": "preview-slug-2",
     *         "type": 2,
     *         "createdAt": 1524306495,
     *         "updatedAt": 1524306495
     *       }
     *     ]
     *   }
     * ]
     *
     * @apiUse 401
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        return new CActiveDataProvider([
            'query'  => $user->getProjects()->with(['featuredScreen', 'previews']),
            'expand' => ['featured', 'previews'],
        ]);
    }

    /**
     * @api {POST} /projects
     * 02. Create project
     * @apiName create
     * @apiGroup Projects
     * @apiDescription
     * Create a new `Project` model.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {String}  title      Project title
     * @apiParam {Number}  type       Project type
     * @apiParam {Number}  [subtype]  Project subtype (**required** only for projects with type `2 - tablet` or `3 - mobile`)
     * @apiParam {String}  [password] Project password (if has any)
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 11,
     *   "title": "My new project",
     *   "createdAt": 1490296356,
     *   "updatedAt": 1490296356,
     *   "featured": null,
     *   "versions": [
     *     {
     *       "id": 23,
     *       "projectId": 11,
     *       "title": "Test version",
     *       "type": 1,
     *       "subtype": null,
     *       "scaleFactor": 1,
     *       "order": 1,
     *       "createdAt": 1490296359,
     *       "updatedAt": 1490296359,
     *       "screens": []
     *     }
     *   ],
     *   "previews": [
     *     {
     *       "id": 1,
     *       "projectId": 11,
     *       "slug": "preview-slug-1",
     *       "type": 1,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     },
     *     {
     *       "id": 2,
     *       "projectId": 11,
     *       "slug": "preview-slug-2",
     *       "type": 2,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     }
     *   ]
     * }
     *
     * @apiUse 401
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": {
     *     "title": "Title cannot be blank.",
     *     "type": "Type is invalid."
     *   }
     * }
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new ProjectForm($user);
        if (
            $model->load(Yii::$app->request->post(), '') &&
            ($project = $model->save())
        ) {
            return $project->toArray([], ['featured', 'versions.screens', 'previews']);
        }

        return $this->setErrorResponse(
            Yii::t('app', 'Oops, an error occurred while processing your request.'),
            $model->getFirstErrors()
        );
    }

    /**
     * @api {PUT} /projects/:id
     * 04. Update project
     * @apiName update
     * @apiGroup Projects
     * @apiDescription
     * Update and return an existing `Project` model owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number}   id               Project id (`GET` parameter)
     * @apiParam {String}   title            Project title
     * @apiParam {Number}   type             Project type
     * @apiParam {Number}   [subtype]        Project subtype (**required** only for projects with type `2 - tablet` or `3 - mobile`)
     * @apiParam {String}   [password]       Project password (if has any)
     * @apiParam {Boolean}  [changePassword] Set to `true` if you want to change/remove the project password
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "id": 11,
     *   "title": "My new project",
     *   "createdAt": 1490296356,
     *   "updatedAt": 1490296356,
     *   "featured": null,
     *   "versions": [
     *     {
     *       "id": 23,
     *       "projectId": 11,
     *       "title": null,
     *       "type": 1,
     *       "subtype": null,
     *       "scaleFactor": 1,
     *       "order": 1,
     *       "createdAt": 1490296359,
     *       "updatedAt": 1490296359,
     *       "screens": []
     *     }
     *   ],
     *   "previews": [
     *     {
     *       "id": 1,
     *       "projectId": 11,
     *       "slug": "preview-slug-1",
     *       "type": 1,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     },
     *     {
     *       "id": 2,
     *       "projectId": 11,
     *       "slug": "preview-slug-2",
     *       "type": 2,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     }
     *   ]
     * }
     *
     *
     * @apiErrorExample {json} 400 Bad Request (example):
     * {
     *   "message": "Oops, an error occurred while processing your request.",
     *   "errors": {
     *     "title": "Title cannot be blank.",
     *     "type": "Type is invalid."
     *   }
     * }
     *
     * @apiUse 401
     *
     * @apiUse 404
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);

        if ($project) {
            $model = new ProjectForm($user);

            if ($model->load(Yii::$app->request->bodyParams, '') && $model->save($project)) {
                return $project->toArray([], ['featured', 'versions.screens', 'previews']);
            }

            return $this->setErrorResponse(
                Yii::t('app', 'Oops, an error occurred while processing your request.'),
                $model->getFirstErrors()
            );
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {GET} /projects/:id
     * 03. View project
     * @apiName view
     * @apiGroup Projects
     * @apiDescription
     * Return an existing `Project` model owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id Project id
     *
     * @apiSuccessExample {json} 200 Success response (example):
     * {
     *   "title": "My new project",
     *   "type": 2,
     *   "subtype": 21,
     *   "createdAt": 1490296356,
     *   "updatedAt": 1490296356,
     *   "id": 11,
     *   "featured": {
     *     "id": 151,
     *     "versionId": 23,
     *     "title": "attachment2",
     *     "hotspots": null,
     *     "order": 1,
     *     "alignment": 0,
     *     "background": null,
     *     "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg",
     *     "createdAt": 1489926572,
     *     "updatedAt": 1489926572,
     *     "thumbs": {
     *       "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg",
     *       "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg"
     *     }
     *   },
     *   "versions": [
     *     {
     *       "id": 23,
     *       "projectId": 11,
     *       "title": "Version 101",
     *       "type": 2,
     *       "subtype": 21,
     *       "scaleFactor": 0,
     *       "order": 1,
     *       "createdAt": 1490296359,
     *       "updatedAt": 1490296359,
     *       "screens": [
     *         {
     *           "id": 151,
     *           "versionId": 23,
     *           "title": "attachment2",
     *           "hotspots": null,
     *           "order": 1,
     *           "alignment": 0,
     *           "background": null,
     *           "imageUrl": "/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38.jpg",
     *           "createdAt": 1489926572,
     *           "updatedAt": 1489926572,
     *           "thumbs": {
     *             "medium": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_medium.jpg",
     *             "small": "http://app.presentator.dev/uploads/projects/45c48cce2e2d7fbdea1afc51c7c6ad26/attachment2_1489926572_38_thumb_small.jpg"
     *           }
     *         }
     *       ]
     *     }
     *   ],
     *   "previews": [
     *     {
     *       "id": 1,
     *       "projectId": 11,
     *       "slug": "preview-slug-1",
     *       "type": 1,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     },
     *     {
     *       "id": 2,
     *       "projectId": 11,
     *       "slug": "preview-slug-2",
     *       "type": 2,
     *       "createdAt": 1524306495,
     *       "updatedAt": 1524306495
     *     }
     *   ]
     * }
     *
     * @apiUse 404
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if ($project) {
            return $project->toArray([], ['featured', 'versions.screens', 'previews']);
        }

        return $this->setNotFoundResponse();
    }

    /**
     * @api {DELETE} /projects/:id
     * 05. Delete project
     * @apiName delete
     * @apiGroup Projects
     * @apiDescription
     * Delete an existing `Project` model owned by the authenticated user.
     *
     * @apiPermission User
     * @apiHeader {String} X-Access-Token User authentication token
     *
     * @apiParam {Number} id Project id
     *
     * @apiUse 204
     *
     * @apiUse 404
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if ($project && $project->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->setNotFoundResponse();
    }
}
