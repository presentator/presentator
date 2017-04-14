<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use common\models\Project;
use common\models\ProjectPreview;
use app\models\ProjectAccessForm;

/**
 * Preview controller that is intentend to handle public projects access.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewController extends AppController
{
    const SESSION_ACCESS_VAR = 'projectAccessVar';

    /**
     * inheritdoc
     * @var string
     */
    public $layout = 'clean';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access']['rules'][] = [
            'actions' => ['view', 'ajax-invoke-access'],
            'allow' => true,
        ];

        $behaviors['verbs']['actions'] = [
            'ajax-invoke-access' => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Renders public project view page.
     * @param  string $slug Preview model slug.
     * @return string
     */
    public function actionView($slug)
    {
        $this->view->params['bodyClass'] = 'preview-page';

        $preview = ProjectPreview::findOneBySlug($slug);
        if (!$preview) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $project       = $preview->project;
        $accessForm    = new ProjectAccessForm($project);
        $grantedAccess = $this->validateSessionAccess($project);

        return $this->render('view', [
            'preview'       => $preview,
            'project'       => $project,
            'accessForm'    => $accessForm,
            'grantedAccess' => $grantedAccess,
        ]);
    }

    /**
     * Validates project access form via ajax.
     * @param  string $slug Preview model slug.
     * @return array
     */
    public function actionAjaxInvokeAccess($slug)
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $preview = ProjectPreview::findOneBySlug($slug);
        if ($preview) {
            $project = $preview->project;
            $accessForm = new ProjectAccessForm($project);

            if (
                !$project->isPasswordProtected() ||                                 // is not password protected
                $this->validateSessionAccess($project) ||                           // has previously invoked access
                ($accessForm->load($request->post()) && $accessForm->grantAccess()) // has entered valid password
            ) {
                Yii::$app->session->set(self::SESSION_ACCESS_VAR, [$project->id => $project->updatedAt]);

                // find the specific active project version
                $versionPos = $request->get('version_pos', -1);
                if ($versionPos >= 0 && isset($project->versions[$versionPos])) {
                    $activeVersion = $project->versions[$versionPos];
                } elseif ($project->latestActiveVersion) {
                    $activeVersion = $project->latestActiveVersion;
                } elseif (isset($project->versions[0])) {
                    $activeVersion = $project->versions[0];
                }

                if ($activeVersion) {
                    $previewHtml = $this->renderPartial('/versions/_preview', [
                        'project'       => $project,
                        'activeVersion' => $activeVersion,
                        'allowComment'  => $preview->type == ProjectPreview::TYPE_VIEW_AND_COMMENT,
                    ]);

                    return [
                        'success'     => true,
                        'previewHtml' => $previewHtml,
                    ];
                }
            }

            return [
                'success' => false,
                'errors'  => $accessForm->getErrors(),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Check if the user has access rights to the project stored in session.
     * NB! If the project is updated after session store the validation will fail.
     * @param  Project $project
     * @return boolean
     */
    protected function validateSessionAccess(Project $project)
    {
        if (!$project->isPasswordProtected()) {
            return true; // no need to check
        }

        $sessionAccess = Yii::$app->session->get(self::SESSION_ACCESS_VAR);

        if (is_array($sessionAccess) && isset($sessionAccess[$project->id])) {
            return $sessionAccess[$project->id] == $project->updatedAt;
        }

        return false;
    }
}
