<?php
namespace presentator\api\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\web\UnauthorizedHttpException;
use yii\helpers\ArrayHelper;
use presentator\api\helpers\CastHelper;
use presentator\api\models\ProjectLink;
use presentator\api\models\Prototype;
use presentator\api\models\forms\ScreenCommentSearch;
use presentator\api\models\forms\PreviewScreenCommentForm;
use presentator\api\models\forms\PreviewScreenCommentStatusChangeForm;

/**
 * Project previews rest API controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class PreviewsController extends ApiController
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator']['optional'] = [
            'authorize',
            'index',
            'prototype',
            'assets',
            'list-screen-comments',
            'create-screen-comment',
            'update-screen-comment',
        ];

        return $behaviors;
    }

    /**
     * Authorizes access to a project preview.
     *
     * @return mixed
     * @throws NotFoundHttpException
     * @throws UnauthorizedHttpException
     */
    public function actionAuthorize()
    {
        $slug     = CastHelper::toString(Yii::$app->request->post('slug', ''));
        $password = CastHelper::toString(Yii::$app->request->post('password', ''));
        $link     = $slug ? ProjectLink::findBySlug($slug) : null;

        if (!$link) {
            throw new NotFoundHttpException();
        }

        if (
            $link->isPasswordProtected() &&
            (!$password || !$link->validatePassword($password))
        ) {
            throw new UnauthorizedHttpException('Missing or invalid project link password.');
        }

        $this->logLoggedUserAccess($link);

        return [
            'token'         => $link->generatePreviewToken(),
            'project'       => $link->project->toArray(),
            'projectLink'   => $link->toArray(),
            'prototypes'    => $link->findAllowedPrototypes(),
            'collaborators' => $link->project->findAllCollaborators(),
        ];
    }

    /**
     * Returns common project link preview info data.
     *
     * @return mixed
     * @throws UnauthorizedHttpException
     */
    public function actionIndex()
    {
        $link = $this->findLinkByPreviewToken();

        $this->logLoggedUserAccess($link);

        return [
            'project'       => $link->project->toArray(),
            'projectLink'   => $link->toArray(),
            'prototypes'    => $link->findAllowedPrototypes(),
            'collaborators' => $link->project->findAllCollaborators(),
        ];
    }

    /**
     * Returns single preview details (screens, hotspots, etc.) for the specified prototype.
     *
     * @param  integer $id ID of the prototype to view.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionPrototype($id)
    {
        $link      = $this->findLinkByPreviewToken();
        $prototype = $link ? $link->findAllowedPrototypeById((int) $id) : null;

        if (!$prototype) {
            throw new NotFoundHttpException();
        }

        Prototype::eagerLoad($prototype->screens, ['hotspots']);
        Prototype::eagerLoad($prototype->hotspotTemplates, ['hotspots', 'hotspotTemplateScreenRels']);

        return $prototype->toArray([], [
            'screens.hotspots',
            'hotspotTemplates.hotspots',
            'hotspotTemplates.screenIds',
        ]);
    }

    /**
     * Returns list with all project guideline sections and assets
     * (if the project link is allowed to list them).
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionAssets()
    {
        $link = $this->findLinkByPreviewToken();

        if (!$link || !$link->allowGuideline) {
            throw new NotFoundHttpException();
        }

        $sections = $link->project->getGuidelineSections()
            ->with('assets')
            ->all();

        $result = [];

        // serialize with assets releation
        foreach ($sections as $section) {
            $result[] = $section->toArray([], ['assets']);
        }

        return $result;
    }

    /**
     * Returns list with all project preview screen comments
     * (if the project link is allowed to list them).
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionListScreenComments()
    {
        $link = $this->findLinkByPreviewToken();

        if (!$link || !$link->allowComments) {
            throw new NotFoundHttpException();
        }

        $searchModel  = new ScreenCommentSearch($link->findAllowedScreenCommentsQuery());
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $dataProvider;
    }

    /**
     * Takes care for creating a new screen comment within the
     * preview screens.
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionCreateScreenComment()
    {
        $link = $this->findLinkByPreviewToken();

        if (!$link || !$link->allowComments) {
            throw new NotFoundHttpException();
        }

        $model = new PreviewScreenCommentForm($link);

        $model->load(Yii::$app->request->post());

        if ($comment = $model->save()) {
            return $comment->toArray([], ['fromUser']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * Updates primary screen comment status within the preview screens.
     *
     * @param  integer $id ID of the primary screen comment to update.
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionUpdateScreenComment($id)
    {
        $link = $this->findLinkByPreviewToken();

        if (
            !$link ||
            !$link->allowComments ||
            !($comment = $link->findAllowedScreenCommentById($id)) ||
            !empty($comment->replyTo) // only primary comments are allowed to be updated
        ) {
            throw new NotFoundHttpException();
        }

        $model = new PreviewScreenCommentStatusChangeForm($comment);

        $model->load(Yii::$app->request->post());

        if ($comment = $model->save()) {
            return $comment->toArray([], ['fromUser']);
        }

        return $this->sendErrorResponse($model->getFirstErrors());
    }

    /**
     * @return ProjectLink
     * @throws UnauthorizedHttpException
     */
    protected function findLinkByPreviewToken(): ProjectLink
    {
        $token = Yii::$app->request->headers->get('X-Preview-Token');

        $link = ProjectLink::findByPreviewToken((string) $token);

        if (!$link) {
            throw new UnauthorizedHttpException('Missing or invalid preview token.');
        }

        return $link;
    }

    /**
     * @param ProjectLink $link
     */
    protected function logLoggedUserAccess(ProjectLink $link)
    {
        if (Yii::$app->user->identity) {
            $link->logUserAccess(Yii::$app->user->identity);
        }
    }
}
