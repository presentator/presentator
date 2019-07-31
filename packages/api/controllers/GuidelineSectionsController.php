<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\GuidelineSectionSearch;
use presentator\api\models\forms\GuidelineSectionForm;

/**
 * GuidelineSections rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GuidelineSectionsController extends ApiController
{
    /**
     * Returns paginated list with guideline sections.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new GuidelineSectionSearch($user->findGuidelineSectionsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `GuidelineSection` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new GuidelineSectionForm($user);

        $model->load(Yii::$app->request->post());

        if ($section = $model->save()) {
            return $section;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `GuidelineSection` model data.
     *
     * @param  integer $id ID of the guideline section to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $section = $user->findGuidelineSectionById($id);
        if (!$section) {
            throw new NotFoundHttpException();
        }

        $model = new GuidelineSectionForm($user, $section);

        $model->load(Yii::$app->request->post());

        if ($section = $model->save()) {
            return $section;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `GuidelineSection` model for detailed view.
     *
     * @param  integer $id ID of the guideline section to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $section = $user->findGuidelineSectionById($id);
        if (!$section) {
            throw new NotFoundHttpException();
        }

        return $section;
    }

    /**
     * Deletes an existing `GuidelineSection` model by its id.
     *
     * @param  integer $id ID of the guideline section to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $section = $user->findGuidelineSectionById($id);
        if (!$section) {
            throw new NotFoundHttpException();
        }

        if ($section->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }
}
