<?php
namespace app\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use common\models\User;
use common\models\Project;
use app\models\ProjectForm;
use app\models\ProjectShareForm;

/**
 * Projects controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ProjectsController extends AppController
{
    use MentionsTrait;

    const ITEMS_PER_PAGE = 14;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs']['actions'] = [
            'delete'                => ['post'],
            'ajax-save-update-form' => ['post'],
            'ajax-share'            => ['post'],
            'ajax-add-admin'        => ['post'],
            'ajax-remove-admin'     => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Renders projects listing page.
     * @return string
     */
    public function actionIndex()
    {
        $user        = Yii::$app->user->identity;
        $projectForm = new ProjectForm();

        if (
            $projectForm->load(Yii::$app->request->post()) &&
            ($project = $projectForm->save($user))
        ) {
            return $this->redirect(['projects/view', 'id' => $project->id]);
        }

        return $this->render('index', [
            'projectForm' => $projectForm,
        ]);
    }

    /**
     * Renders projects listing page.
     * @param  integer $id
     * @return string
     */
    public function actionView($id)
    {
        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);

        if (!$project) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $this->view->params['bodyClass'] = 'with-secondary-sidebar';

        $screenIds = [];
        foreach ($project->versions as $version) {
            $screenIds = array_merge($screenIds, ArrayHelper::getColumn($version->screens, 'id'));
        }
        $commentCounters = $user->countUnreadCommentsByScreens($screenIds);

        $mentionsList = $this->getMentionsList($project);

        $shareForm = new ProjectShareForm($project);

        return $this->render('view', [
            'project'         => $project,
            'shareForm'       => $shareForm,
            'commentCounters' => $commentCounters,
            'mentionsList'    => $mentionsList,
        ]);
    }

    /**
     * Deletes a single project model.
     * @param  integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);

        if (!$project) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        if ($project->delete()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'The project was successfully deleted.'));
        } else {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Oops, an error occurred while processing your request.'));
        }

        $this->redirect(['projects/index']);
    }

    /**
     * Renders update project form via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxGetUpdateForm($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);

        if ($project) {
            $projectForm = new ProjectForm($project);

            $this->layout = 'blank';

            return [
                'success'    => true,
                'updateForm' => $this->render('_form', [
                    'model' => $projectForm,
                ]),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Handles project model update via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxSaveUpdateForm($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);

        if ($project) {
            $projectForm = new ProjectForm($project);

            if ($projectForm->load(Yii::$app->request->post()) && $projectForm->save()) {
                return [
                    'success' => true,
                    'project' => $project->toArray(),
                    'message' => Yii::t('app', 'Successfully updated project settings.')
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Renders filtered projects dropdown items.
     * @param  string  $search
     * @param  boolean $mustBeOwner Flag to filter only super user owned projects (for regular users this flag is ignored and it is always `true`)
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxSearchProjects($search, $mustBeOwner = true)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        // normalize super user owner flag parameter
        $mustBeOwner = is_bool($mustBeOwner) ? $mustBeOwner : ($mustBeOwner === 'true');

        // normalize search param
        $search = trim($search);

        if (strlen($search) >= 2) {
            $user            = Yii::$app->user->identity;
            $projects        = $user->searchProjects($search, 200, 0, $mustBeOwner);
            $commentCounters = $user->countUnreadCommentsByProjects(ArrayHelper::getColumn($projects, 'id'));

            $projectsHtml = '';
            foreach ($projects as $project) {
                $projectsHtml .= $this->renderPartial('_item', [
                    'model'       => $project,
                    'newComments' => ArrayHelper::getValue($commentCounters, $project->id, 0),
                ]);
            }

            return [
                'success'      => true,
                'projectsHtml' => $projectsHtml,
            ];
        }

        return [
            'success' => false,
            // there is no need for an error message
            // 'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Loads project items via ajax.
     * @param  integer $page
     * @param  boolean $mustBeOwner Flag to filter only super user owned projects (for regular users this flag is ignored and it is always `true`)
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxLoadMore($page = 1, $mustBeOwner = true)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        // normalize page parameter
        $page   = $page ? (int) $page : 1;
        $offset = (($page - 1) * self::ITEMS_PER_PAGE);

        // normalize super user owner flag parameter
        $mustBeOwner = is_bool($mustBeOwner) ? $mustBeOwner : ($mustBeOwner === 'true');

        // fetch projects
        $user               = Yii::$app->user->identity;
        $projects           = $user->findProjects(self::ITEMS_PER_PAGE, $offset, $mustBeOwner);
        $totalProjectsCount = $user->countProjects($mustBeOwner);
        $commentCounters    = $user->countUnreadCommentsByProjects(ArrayHelper::getColumn($projects, 'id'));

        $projectsHtml = '';
        foreach ($projects as $project) {
            $projectsHtml .= $this->renderPartial('_item', [
                'model'       => $project,
                'newComments' => ArrayHelper::getValue($commentCounters, $project->id, 0),
            ]);
        }

        $hasMoreProjects = true;
        if ($totalProjectsCount <= ($page * self::ITEMS_PER_PAGE)) {
            $hasMoreProjects = false;
        }

        return [
            'success'         => true,
            'projectsHtml'    => $projectsHtml,
            'hasMoreProjects' => $hasMoreProjects,
        ];
    }

    /**
     * Sends Project preview url email via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxShare($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);

        if ($project) {
            $shareForm = new ProjectShareForm($project);

            if ($shareForm->load(Yii::$app->request->post()) && $shareForm->send()) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Successfully shared.'),
                ];
            }

            return [
                'success' => false,
                'errors'  => $shareForm->getErrors(),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Unlink administrator from a project via ajax.
     * Requires the following POST params:
     * - `userId`
     * - `projectId`
     *
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxRemoveAdmin()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user     = Yii::$app->user->identity;
        $project  = $user->findProjectById($request->post('projectId', -1));
        $oldAdmin = User::findIdentity($request->post('userId', -1));

        if ($project && $oldAdmin) {
            $selfUnlink = $user->id == $oldAdmin->id ? true : false;

            if ($project->unlinkUser($oldAdmin, !$selfUnlink)) {
                return [
                    'success'     => true,
                    'message'     => Yii::t('app', 'User was unlinked successfully.'),
                    'redirectUrl' => ($selfUnlink ? Url::to(['projects/index'], true) : ''),
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Link user to a project via ajax.
     * Requires the following POST params:
     * - `userId`
     * - `projectId`
     *
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxAddAdmin()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($request->post('projectId', -1));
        $admin   = User::findIdentity($request->post('userId', -1));

        if ($project && $admin && $project->linkUser($admin)) {
            $listItemHtml = $this->renderPartial('_admin_list_item', ['user' => $admin, 'project' => $project]);

            return [
                'success'      => true,
                'listItemHtml' => $listItemHtml,
                'message'      => Yii::t('app', 'User was linked successfully.'),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Searchs and returns list with new project users via ajax.
     * @param  string $id     The project id to search users for.
     * @param  string $search Search term.
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxSearchUsers($id, $search)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($id);
        $search  = trim($search);

        if ($project && strlen($search) >= 2) {
            $currentAdminIds = ArrayHelper::getColumn($project->users, 'id');
            $users           = User::searchUsers($search, $currentAdminIds, Yii::$app->params['fuzzyUsersSearch']);
            $suggestionsHtml = $this->renderPartial('/users/_suggestions', ['users' => $users]);

            return [
                'success'         => true,
                'suggestionsHtml' => $suggestionsHtml,
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }
}
