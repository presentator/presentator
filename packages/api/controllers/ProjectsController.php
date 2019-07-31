<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use presentator\api\models\User;
use presentator\api\models\forms\ProjectSearch;
use presentator\api\models\forms\ProjectForm;

/**
 * Projects rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectsController extends ApiController
{
    /**
     * Returns paginated list with `Project` models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $user = Yii::$app->user->identity;

        $searchModel  = new ProjectSearch($user->findProjectsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Creates a new `Project` model owned by the authenticated user.
     *
     * @return mixed
     */
    public function actionCreate()
    {
        $user = Yii::$app->user->identity;

        $model = new ProjectForm($user);

        $model->load(Yii::$app->request->post());

        if ($project = $model->save()) {
            return $project->toArray([], ['featuredScreen']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates an existing `Project` model data.
     *
     * @param  integer $id ID of the project to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        $model = new ProjectForm($user, $project);

        $model->load(Yii::$app->request->post());

        if ($project = $model->save()) {
            return $project->toArray([], ['featuredScreen']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Returns an existing `Project` model for detailed view.
     *
     * @param  integer $id ID of the project to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        return $project->toArray([], ['featuredScreen']);
    }

    /**
     * Deletes an existing `Project` model by its id.
     *
     * @param  integer $id ID of the project to delete.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        if ($project->delete()) {
            Yii::$app->response->statusCode = 204;

            return null;
        }

        return $this->sendErrorResponse();
    }

    /**
     * Returns list with all project's collaborators - linked users and screen commentators (including guests).
     *
     * @param  integer $id ID of the project to return collaborators for.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionListCollaborators($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        return $project->findAllCollaborators();
    }

    /**
     * Searches for new users to link (aka. adding new project admins).
     *
     * @param  integer $id     ID of the project to search users for.
     * @param  string  $search Search term to apply to the search query.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionSearchUsers($id, $search)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        if (is_string($search) && strlen($search) >= 2) {
            $currentUserIds = ArrayHelper::getColumn($project->userProjectRels, 'userId');
            $users          = User::searchUsers($search, $currentUserIds, Yii::$app->params['looseProjectUsersSearch']);

            return ArrayHelper::toArray($users, [
                User::class => ['id', 'email', 'firstName', 'lastName', 'avatar'],
            ]);
        }

        return [];
    }

    /**
     * Returns list with all linked project users.
     *
     * @param  integer $id Project ID.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionListUsers($id)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException();
        }

        return ArrayHelper::toArray($project->users, [
            User::class => ['id', 'email', 'firstName', 'lastName', 'avatar'],
        ]);
    }

    /**
     * Links an user to a project.
     *
     * @param  integer $id     ID of the project to link user to.
     * @param  integer $userId ID of the user to link.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionLinkUser($id, $userId)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException('The requested project does not exist or you do not have access to.');
        }

        $userToLink = User::findById($userId);
        if (!$userToLink) {
            throw new NotFoundHttpException('The user to link does not exist or is inactive.');
        }

        try {
            if (!$userToLink->isLinkedToProject($project)) {
                $project->linkOnce('users', $userToLink);

                $userToLink->sendLinkedToProjectEmail($project);
            }

            Yii::$app->response->statusCode = 204;

            return null;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse();
    }

    /**
     * Unlinks an user from a project.
     *
     * @param  integer $id     ID of the project to unlink user from.
     * @param  integer $userId ID of the user to unlink.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUnlinkUser($id, $userId)
    {
        $user = Yii::$app->user->identity;

        $project = $user->findProjectById($id);
        if (!$project) {
            throw new NotFoundHttpException('The requested project does not exist or you do not have access to.');
        }

        $userToUnlink = User::findById($userId);
        if (!$userToUnlink) {
            throw new NotFoundHttpException('The user to unlink does not exist or is inactive.');
        }

        if (!$user->isSuperUser() && count($project->userProjectRels) == 1) {
            return $this->sendErrorResponse([], Yii::t('app', 'You cannot unlink the only user of the project.'));
        }

        try {
            if ($userToUnlink->isLinkedToProject($project)) {
                $project->unlink('users', $userToUnlink, true);

                $userToUnlink->sendUnlinkedFromProjectEmail($project);
            }

            Yii::$app->response->statusCode = 204;

            return null;
        } catch (\Exception | \Throwable $e) {
            Yii::error($e->getMessage());
        }

        return $this->sendErrorResponse();
    }
}
