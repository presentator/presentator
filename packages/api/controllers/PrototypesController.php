<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use presentator\api\models\forms\PrototypeSearch;
use presentator\api\models\forms\PrototypeForm;

/**
 * Prototypes rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PrototypesController extends ApiController
{
    /**
     * Returns paginated list with `Prototype` models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new PrototypeSearch($user->findPrototypesQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `Prototype` model.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new PrototypeForm($user);

        $model->load(Yii::$app->request->post());

        if ($prototype = $model->save()) {
            return $prototype;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `Prototype` model data.
     *
     * @param  integer $id ID of the prototype to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $prototype = $user->findPrototypeById($id);
        if (!$prototype) {
            throw new NotFoundHttpException();
        }

        $model = new PrototypeForm($user, $prototype);

        $model->load(Yii::$app->request->post());

        if ($prototype = $model->save()) {
            return $prototype;
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `Prototype` model for detailed view.
     *
     * @param  integer $id ID of the prototype to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $prototype = $user->findPrototypeById($id);
        if (!$prototype) {
            throw new NotFoundHttpException();
        }

        return $prototype;
    }

    /**
     * Deletes an existing `Prototype` model by its id.
     *
     * @param  integer $id ID of the prototype to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $prototype = $user->findPrototypeById($id);
        if (!$prototype) {
            throw new NotFoundHttpException();
        }

        if ($prototype->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Duplicates an existing `Prototype` model.
     *
     * @param  integer $id ID of the prototype to duplicate.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDuplicate($id)
    {
        $user = Yii::$app->user->identity;

        $prototype = $user->findPrototypeById($id);
        if (!$prototype) {
            throw new NotFoundHttpException();
        }

        return $prototype->duplicate((string) Yii::$app->request->post('title'));
    }
}
