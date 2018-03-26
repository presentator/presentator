<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\data\Pagination;
use common\models\User;
use app\models\SuperUserForm;

/**
 * SuperUserActionsTrait class that handles super user controller actions.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
trait SuperUserActionsTrait
{
    /**
     * Renders users listing page.
     * @return string
     */
    public function actionIndex()
    {
        $totalCount = User::countUsers();

        $pagination = new Pagination([
            'totalCount' => $totalCount,
            'pageSize'   => self::ITEMS_PER_PAGE,
        ]);

        $users = User::findUsers($pagination->limit, $pagination->offset);

        return $this->render('index', [
            'users'      => $users,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Searchs and returns list with user models via ajax.
     * @param  string $search
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxSearchUsers($search)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $search = trim($search);
        if (strlen($search) >= 2) {

            $users = User::searchUsers($search, [], true, false, self::MAX_SEARCH_RESULTS);

            $listHtml = $this->renderPartial('_users_list', ['users' => $users]);

            return [
                'success'  => true,
                'listHtml' => $listHtml,
            ];
        }

        return [
            'success' => false,
        ];
    }

    /**
     * Creates a new User model.
     * @return mixed
     */
    public function actionCreate()
    {
        $form = new SuperUserForm(null, ['scenario' => SuperUserForm::SCENARIO_CREATE]);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully created a new user.'));

            return $this->redirect(['users/index']);
        }

        $statusesList = User::getStatusLabels();
        $typesList    = User::getTypeLabels();

        return $this->render('create', [
            'form'         => $form,
            'statusesList' => $statusesList,
            'typesList'    => $typesList,
        ]);
    }

    /**
     * Updates an existing User model.
     * @param  integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $form = new SuperUserForm($user, ['scenario' => SuperUserForm::SCENARIO_UPDATE]);

        if ($form->load(Yii::$app->request->post()) && $form->save()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'Successfully updated user.'));

            return $this->redirect(['users/index']);
        }

        $statusesList = User::getStatusLabels();
        $typesList    = User::getTypeLabels();

        return $this->render('update', [
            'form'         => $form,
            'user'         => $user,
            'statusesList' => $statusesList,
            'typesList'    => $typesList,
        ]);
    }

    /**
     * Deletes a single User model.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if (
            $user->id != Yii::$app->user->identity->id && // is not the current authenticated user
            $user->delete()
        ) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'The user was successfully deleted.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Oops, an error occurred while processing your request.'));
        }

        $this->redirect(['users/index']);
    }
}
