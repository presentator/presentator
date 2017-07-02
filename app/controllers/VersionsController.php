<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\models\Version;
use app\models\VersionForm;

/**
 * Versions controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class VersionsController extends AppController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs']['actions'] = [
            'ajax-delete'    => ['post'],
            'ajax-save-form' => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Deletes a  single Version model via ajax.
     *
     * NB! Requires the following post parameters:
     * `id` - ID of the version to delete
     *
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxDelete()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById(Yii::$app->request->post('id', -1));

        if ($version) {
            if ($version->isTheOnlyOne()) {
                return [
                    'success' => false,
                    'message' => Yii::t('app', 'You can not delete the only one project version.'),
                ];
            }

            if ($version->delete()) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Successfully deleted verion.'),
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Returns and renders version create/update form via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxGetForm($projectId, $versionId = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($projectId);

        if ($project) {
            $version = $project->findVersionById($versionId);

            $model = new VersionForm($project, $version);

            $this->layout = 'blank';

            return [
                'success'  => true,
                'formHtml' => $this->render('_form', ['model' => $model]),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Saves version create/update form via ajax.
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxSaveForm($projectId)
    {
        $request = Yii::$app->request;

        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $project = $user->findProjectById($projectId);

        if ($project) {
            $version  = $project->findVersionById($request->post('versionId', -1));
            $model    = new VersionForm($project, $version);
            $isUpdate = $model->isUpdate();

            if ($model->load($request->post()) && $model->save()) {
                $navItemHtml = $this->renderPartial('_nav_item', ['model' => $model->version]);

                if ($isUpdate) {
                    $message         = Yii::t('app', 'Successfully saved changes.');
                    $contentItemHtml = '';
                } else {
                    $message         = Yii::t('app', 'Successfully created new verion.');
                    $contentItemHtml = $this->renderPartial('_content_item', ['model' => $model->version]);
                }

                return [
                    'success'         => true,
                    'isUpdate'        => $isUpdate,
                    'version'         => $model->version->toArray(),
                    'navItemHtml'     => $navItemHtml,
                    'contentItemHtml' => $contentItemHtml,
                    'message'         => $message,
                ];
            }

        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Generates and returns screens slider html.
     * @param  integer `versionId`          ID of the version to load.
     * @param  null|integer `screenId`      Optional parameter to mark the specific screen as active.
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxGetScreensSlider($versionId, $screenId = null)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById($versionId);

        if ($version) {
            Version::eagerLoad($version->screens, ['screenComments.loginUserRel']);
            $unreadCommentTargets = $this->getUnreadCommentTargets($version);
            $screensSliderHtml    = $this->renderPartial('_screens_slider', [
                'model'                => $version,
                'activeScreenId'       => $screenId,
                'unreadCommentTargets' => $unreadCommentTargets,
            ]);

            return [
                'success'           => true,
                'screensSliderHtml' => $screensSliderHtml,
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Helper to generate an array with unread commen target ids.
     * @param  Version $version
     * @return array
     */
    protected function getUnreadCommentTargets(Version $version)
    {
        $result = [];

        foreach ($version->screens as $screen) {
            foreach ($screen->screenComments as $comment) {
                if (!$comment->replyTo) {
                    if (!$comment->isReadByLoginUser()) {
                        $result[] = $comment->id;
                    }
                } elseif (!$comment->isReadByLoginUser()) {
                    $result[] = $comment->replyTo;
                }
            }
        }

        return $result;
    }
}
