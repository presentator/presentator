<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\HotspotSearch;
use presentator\api\models\forms\HotspotForm;

/**
 * Hotspots rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotsController extends ApiController
{
    /**
     * Returns paginated list with `Hotspot` models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new HotspotSearch($user->findHotspotsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `Hotspot` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new HotspotForm($user);

        $model->load(Yii::$app->request->post());

        if ($hotspot = $model->save()) {
            return $hotspot;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `Hotspot` model data.
     *
     * @param  integer $id ID of the hotspot to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $hotspot = $user->findHotspotById($id);
        if (!$hotspot) {
            throw new NotFoundHttpException();
        }

        $model = new HotspotForm($user, $hotspot);

        $model->load(Yii::$app->request->post());

        if ($hotspot = $model->save()) {
            return $hotspot;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `Hotspot` model for detailed view.
     *
     * @param  integer $id ID of the hotspot to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $hotspot = $user->findHotspotById($id);
        if (!$hotspot) {
            throw new NotFoundHttpException();
        }

        return $hotspot;
    }

    /**
     * Deletes an existing `Hotspot` model by its id.
     *
     * @param  integer $id ID of the hotspot to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $hotspot = $user->findHotspotById($id);
        if (!$hotspot) {
            throw new NotFoundHttpException();
        }

        if ($hotspot->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }
}
