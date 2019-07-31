<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use presentator\api\models\GuidelineAsset;
use presentator\api\models\forms\GuidelineAssetSearch;
use presentator\api\models\forms\GuidelineAssetForm;

/**
 * GuidelineAssets rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineAssetsController extends ApiController
{
    /**
     * Returns paginated list with guideline colors.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new GuidelineAssetSearch($user->findGuidelineAssetsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `GuidelineAsset` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $scenario = Yii::$app->request->post('type') == GuidelineAsset::TYPE['FILE'] ?
            GuidelineAssetForm::SCENARIO_FILE_CREATE : GuidelineAssetForm::SCENARIO_COLOR_CREATE;

        $model = new GuidelineAssetForm($user, null, ['scenario' => $scenario]);

        $model->load(Yii::$app->request->post());

        $model->file = UploadedFile::getInstanceByName('file');

        if ($asset = $model->save()) {
            return $asset;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `GuidelineAsset` model data.
     *
     * @param  integer $id ID of the guideline asset to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $asset = $user->findGuidelineAssetById($id);
        if (!$asset) {
            throw new NotFoundHttpException();
        }

        $scenario = $asset->type == GuidelineAsset::TYPE['FILE'] ?
            GuidelineAssetForm::SCENARIO_FILE_UPDATE : GuidelineAssetForm::SCENARIO_COLOR_UPDATE;

        $model = new GuidelineAssetForm($user, $asset, ['scenario' => $scenario]);

        $model->load(Yii::$app->request->post());

        if ($asset = $model->save()) {
            return $asset;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `GuidelineAsset` model for detailed view.
     *
     * @param  integer $id ID of the guideline asset to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $asset = $user->findGuidelineAssetById($id);
        if (!$asset) {
            throw new NotFoundHttpException();
        }

        return $asset;
    }

    /**
     * Deletes an existing `GuidelineAsset` model by its id.
     *
     * @param  integer $id ID of the guideline asset to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $asset = $user->findGuidelineAssetById($id);
        if (!$asset) {
            throw new NotFoundHttpException();
        }

        if ($asset->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }
}
