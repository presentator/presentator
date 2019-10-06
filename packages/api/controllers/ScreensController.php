<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\ScreenSearch;
use presentator\api\models\forms\ScreenForm;

/**
 * Screens rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensController extends ApiController
{
    /**
     * Returns paginated list with `Screen` models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new ScreenSearch($user->findScreensQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `Screen` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new ScreenForm($user, null, [
            'scenario' => ScreenForm::SCENARIO_CREATE,
        ]);

        $model->load(Yii::$app->request->post());

        $model->file = UploadedFile::getInstanceByName('file');

        if ($screen = $model->save()) {
            return $screen;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `Screen` model data.
     *
     * @param  integer $id ID of the screen to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $screen = $user->findScreenById($id);
        if (!$screen) {
            throw new NotFoundHttpException();
        }

        $model = new ScreenForm($user, $screen, [
            'scenario' => ScreenForm::SCENARIO_UPDATE,
        ]);

        $model->load(Yii::$app->request->post());

        $model->file = UploadedFile::getInstanceByName('file');

        if ($screen = $model->save()) {
            return $screen;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `Screen` model for detailed view.
     *
     * @param  integer $id ID of the screen to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $screen = $user->findScreenById($id);
        if (!$screen) {
            throw new NotFoundHttpException();
        }

        return $screen;
    }

    /**
     * Deletes an existing `Screen` model by its id.
     *
     * @param  integer $id ID of the screen to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $screen = $user->findScreenById($id);
        if (!$screen) {
            throw new NotFoundHttpException();
        }

        if ($screen->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }
}
