<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\models\Version;

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
            'ajax-create' => ['post'],
            'ajax-delete' => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Creates new Version model via ajax.
     *
     * NB! Requires the following post parameters:
     * `projectId` - ID of the project to which will be attached the new version
     *
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxCreate()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user      = Yii::$app->user->identity;
        $projectId = Yii::$app->request->post('projectId', -1);
        $project   = $user->findProjectById($projectId);

        if ($project) {
            $version = new Version;
            $version->projectId = $project->id;

            if ($version->save()) {
                $navItemHtml     = $this->renderPartial('/versions/_nav_item', ['model' => $version]);
                $contentItemHtml = $this->renderPartial('/versions/_content_item', ['model' => $version]);

                return [
                    'success'         => true,
                    'navItemHtml'     => $navItemHtml,
                    'contentItemHtml' => $contentItemHtml,
                    'message'         => Yii::t('app', 'Successfully created new verion.'),
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
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
            $screensSliderHtml    = $this->renderPartial('/versions/_screens_slider', [
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
