<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\data\Pagination;
use common\models\User;

/**
 * Super users controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class SuperController extends AppController
{
    const ITEMS_PER_PAGE     = 20;
    const MAX_SEARCH_RESULTS = 50;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'] = [
            [
                'actions' => [
                    'index',
                    'delete',
                    'ajax-search-users',
                ],
                'allow' => true,
                'roles' => ['@'],
                'matchCallback' => function ($rule, $action) {
                    $user = Yii::$app->user->identity;

                    return $user->type == User::TYPE_SUPER;
                },
            ]
        ];

        $behaviors['verbs']['actions'] = [
            'delete' => ['post'],
        ];

        return $behaviors;
    }

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
     * Deletes a single User model.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $user        = User::findOne($id);

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

        $this->redirect(['super/index']);
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
}
