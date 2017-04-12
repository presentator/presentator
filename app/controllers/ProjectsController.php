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
        $projectForm = new ProjectForm(null, ['type' => Project::TYPE_DESKTOP]);

        if (
            $projectForm->load(Yii::$app->request->post()) &&
            ($project = $projectForm->save($user))
        ) {
            return $this->redirect(['projects/view', 'id' => $project->id]);
        }

        $projects        = $user->findProjects(self::ITEMS_PER_PAGE);
        $hasMoreProjects = $user->countProjects() > count($projects);
        $projectIds      = ArrayHelper::getColumn($projects, 'id');
        $commentCounters = $user->countUnreadCommentsByProjects($projectIds);

        return $this->render('index', [
            'projects'        => $projects,
            'projectForm'     => $projectForm,
            'typesList'       => Project::getTypeLabels(),
            'hasMoreProjects' => $hasMoreProjects,
            'commentCounters' => $commentCounters,
            'subtypesList'    => [
                Project::TYPE_TABLET => Project::getTabletSubtypeLabels(),
                Project::TYPE_MOBILE => Project::getMobileSubtypeLabels(),
            ],
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

        $shareForm = new ProjectShareForm($project);

        return $this->render('view', [
            'project'         => $project,
            'shareForm'       => $shareForm,
            'commentCounters' => $commentCounters,
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
                    'model'        => $projectForm,
                    'typesList'    => Project::getTypeLabels(),
                    'subtypesList' => [
                        Project::TYPE_TABLET => Project::getTabletSubtypeLabels(),
                        Project::TYPE_MOBILE => Project::getMobileSubtypeLabels(),
                    ],
                    'isNewRecord' => false,
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
     * @param  string $search
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxSearchProjects($search)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user = Yii::$app->user->identity;
        $search = trim($search);
        if (strlen($search) >= 2) {
            $projects = $user->searchProjects($search);

            $projectsHtml = '';
            foreach ($projects as $project) {
                $projectsHtml .= $this->renderPartial('_item', ['model' => $project]);
            }

            return [
                'success'      => true,
                'projectsHtml' => $projectsHtml,
            ];
        }

        return [
            'success' => false,
            // there is no need for error message
            // 'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Loads project items via ajax.
     * @param  integet $page
     * @return array
     * @throws BadRequestHttpException For none ajax requests
     */
    public function actionAjaxLoadMore($page = 1)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        // normalize page
        $page   = $page ? (int) $page : 1;
        $offset = (($page - 1) * self::ITEMS_PER_PAGE);

        $user               = Yii::$app->user->identity;
        $projects           = $user->findProjects(self::ITEMS_PER_PAGE, $offset);
        $totalProjectsCount = $user->countProjects();

        $projectsHtml = '';
        foreach ($projects as $project) {
            $projectsHtml .= $this->renderPartial('_item', ['model' => $project]);
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
     * @param  integet $id
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
            $users           = User::searchUsers($search, $currentAdminIds, 20);
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
