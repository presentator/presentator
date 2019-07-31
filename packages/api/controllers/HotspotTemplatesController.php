<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\HotspotTemplateSearch;
use presentator\api\models\forms\HotspotTemplateForm;

/**
 * HotspotTemplates rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotTemplatesController extends ApiController
{
    /**
     * Returns paginated list with `HotspotTemplate` model.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new HotspotTemplateSearch($user->findHotspotTemplatesQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `HotspotTemplate` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new HotspotTemplateForm($user, null, [
            'scenario' => HotspotTemplateForm::SCENARIO_CREATE,
        ]);

        $model->load(Yii::$app->request->post());

        if ($template = $model->save()) {
            return $template;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `HotspotTemplate` model data.
     *
     * @param  integer $id ID of the template to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException();
        }

        $model = new HotspotTemplateForm($user, $template, [
            'scenario' => HotspotTemplateForm::SCENARIO_UPDATE,
        ]);

        $model->load(Yii::$app->request->post());

        if ($template = $model->save()) {
            return $template;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `HotspotTemplate` model for detailed view.
     *
     * @param  integer $id ID of the template to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException();
        }

        return $template;
    }

    /**
     * Deletes an existing `HotspotTemplate` model by its id.
     *
     * @param  integer $id ID of the template to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException();
        }

        if ($template->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Returns list with all linked hotspot screens.
     *
     * @param  integer $id HotspotTemplate ID.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionListScreens($id)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException();
        }

        return $template->screens;
    }

    /**
     * Links a screen to a hotspot template.
     *
     * @param  integer $id       ID of the screen to link.
     * @param  integer $screenId ID of the screen to link.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionLinkScreen($id, $screenId)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException('The requested template does not exist or you do not have access to it.');
        }

        $screenToLink = $user->findScreenById($screenId, [
            'prototypeId' => $template->prototypeId
        ]);
        if (!$screenToLink) {
            throw new NotFoundHttpException('The screen to link does not exist or is from another prototype model.');
        }

        try {
            $template->linkOnce('screens', $screenToLink);

            Yii::$app->response->statusCode = 204;

            return null;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse();
    }

    /**
     * Unlinks a screen from a hotspot template.
     *
     * @param  integer $id       ID of the template to unlink the screen from.
     * @param  integer $screenId ID of the screen to unlink.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUnlinkScreen($id, $screenId)
    {
        $user = Yii::$app->user->identity;

        $template = $user->findHotspotTemplateById($id);
        if (!$template) {
            throw new NotFoundHttpException('The requested template does not exist or you do not have access to it.');
        }

        $screenToUnlink = $user->findScreenById($screenId, ['prototypeId' => $template->prototypeId]);
        if (!$screenToUnlink) {
            throw new NotFoundHttpException('The screen to unlink does not exist or is from another prototype model.');
        }

        try {
            $template->unlink('screens', $screenToUnlink, true);

            Yii::$app->response->statusCode = 204;

            return null;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse();
    }
}
