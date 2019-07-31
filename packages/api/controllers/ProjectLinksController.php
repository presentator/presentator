<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\ProjectLinkSearch;
use presentator\api\models\forms\ProjectLinkForm;
use presentator\api\models\forms\ProjectLinkShareForm;

/**
 * ProjectLinks rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectLinksController extends ApiController
{
    /**
     * Returns paginated list with project links.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new ProjectLinkSearch($user->findProjectLinksQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new project link.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new ProjectLinkForm($user);

        $model->load(Yii::$app->request->post());

        if ($projectLink = $model->save()) {
            return $projectLink->toArray([], ['prototypes']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `ProjectLink` model data.
     *
     * @param  integer $id ID of the project link to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $projectLink = $user->findProjectLinkById($id);
        if (!$projectLink) {
            throw new NotFoundHttpException();
        }

        $model = new ProjectLinkForm($user, $projectLink);

        $model->load(Yii::$app->request->post());

        if ($projectLink = $model->save()) {
            return $projectLink->toArray([], ['prototypes']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `ProjectLink` model detailed view.
     *
     * @param  integer $id ID of the project link to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $projectLink = $user->findProjectLinkById($id);
        if (!$projectLink) {
            throw new NotFoundHttpException();
        }

        return $projectLink->toArray([], ['prototypes']);
    }

    /**
     * Deletes an existing `ProjectLink` model by its id.
     *
     * @param  integer $id ID of the project link to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $projectLink = $user->findProjectLinkById($id);
        if (!$projectLink) {
            throw new NotFoundHttpException();
        }

        if ($projectLink->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Sends an email with project link info.
     *
     * @param  integer $id ID of the project link to share.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionShare($id)
    {
        $user = Yii::$app->user->identity;

        $projectLink = $user->findProjectLinkById($id);
        if (!$projectLink) {
            throw new NotFoundHttpException();
        }

        $model = new ProjectLinkShareForm($projectLink);

        $model->load(Yii::$app->request->post());

        if ($model->send()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }
}
