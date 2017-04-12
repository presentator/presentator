<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\components\web\CUploadedFile;
use common\models\Screen;
use app\models\ScreensUploadForm;
use app\models\ScreenSettingsForm;

/**
 * Screens controller.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class ScreensController extends AppController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs']['actions'] = [
            'ajax-upload'             => ['post'],
            'ajax-delete'             => ['post'],
            'ajax-save-settings-form' => ['post'],
            'ajax-reorder'            => ['post'],
            'ajax-save-hotspots'      => ['post'],
            'ajax-move-screens'       => ['post'],
        ];

        return $behaviors;
    }

    /**
     * Uploads screens via ajax.
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxUpload()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $version = $user->findVersionById(Yii::$app->request->post('versionId', -1));
        if ($version) {
            $uploadForm         = new ScreensUploadForm($version);
            $uploadForm->images = CUploadedFile::getInstances($uploadForm, 'images');

            if ($screens = $uploadForm->save()) {
                $screenIds     = [];
                $listItemsHtml = '';
                foreach ($screens as $screen) {
                    $screenIds[] = $screen->id;

                    $listItemsHtml .= $this->renderPartial('/screens/_list_item', ['model' => $screen]);
                }

                return [
                    'success'       => true,
                    'screenIds'     => $screenIds,
                    'listItemsHtml' => $listItemsHtml,
                ];
            }

            return [
                'success' => false,
                'message' => implode('<br/>', $uploadForm->getFirstErrors()),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Reorders screens via ajax.
     *
     * NB! Requires the following post parameters:
     * `id`       - ID of the targeted screen model
     * `position` - the new screen model order position
     *
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxReorder()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user     = Yii::$app->user->identity;
        $position = Yii::$app->request->post('position', -1);
        $screen   = $user->findScreenById(Yii::$app->request->post('id', -1));

        if ($screen && $screen->moveToPosition($position)) {
            return [
                'success' => true,
                'message' => Yii::t('app', 'Successfully saved changes.'),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Deletes screen(s) via ajax.
     *
     * NB! Requires the following post parameters:
     * `id` - ID(s) of the screen(s) to delete (pass an array for bulk delete)
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
        $screens = $user->findScreensQuery(Yii::$app->request->post('id', -1))->all();

        if (!empty($screens)) {
            $result = true;
            foreach ($screens as $screen) {
                $result = $result && $screen->delete();
            }

            if ($result) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Successfully deleted screen(s).'),
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Returns and renders screen settings form via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxGetSettingsForm($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user   = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);

        if ($screen) {
            $model = new ScreenSettingsForm($screen);

            $this->layout = 'blank';

            return [
                'success'  => true,
                'formHtml' => $this->render('/screens/_form', ['model' => $model]),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Persists screen settings form via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxSaveSettingsForm($id)
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user   = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);

        if ($screen) {
            $model = new ScreenSettingsForm($screen);

            // submit settings form
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                if ($screen->alignment == Screen::ALIGNMENT_LEFT) {
                    $alignment = 'left';
                } elseif ($screen->alignment == Screen::ALIGNMENT_RIGHT) {
                    $alignment = 'right';
                } else {
                    $alignment = 'center';
                }

                return [
                    'success' => true,
                    'settings' => [
                        'title'      => $screen->title,
                        'background' => $screen->background,
                        'alignment'  => $alignment,
                    ],
                    'message' => Yii::t('app', 'Successfully saved changes.'),
                ];
            }

            return [
                'success' => false,
                'message' => implode('<br>', $model->getFirstErrors()),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Updates screen hotposts data via ajax.
     *
     * NB! Requires the following post parameters:
     * `id`       - ID of the screen to update
     * `hotspots` - array with hotspots configurations
     *
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxSaveHotspots()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user     = Yii::$app->user->identity;
        $screen   = $user->findScreenById(Yii::$app->request->post('id', -1));
        $hotspots = Yii::$app->request->post('hotspots', null);

        if (is_array($hotspots)) {
            $hotspots = json_encode($hotspots);
        } elseif (is_string($hotspots)) {
            $hotspots = $hotspots;
        } else {
            $hotspots = null;
        }

        if ($this->checkHotspotsFormat($hotspots) && $screen) {
            $screen->hotspots = $hotspots;

            if ($screen->save()) {
                return [
                    'success' => true,
                ];
            }

            return [
                'success' => false,
                'message' => implode('<br>', $screen->getFirstErrors()),
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Moves screens from one version to another via ajax.
     *
     * NB! Requires the following post parameters:
     * `screenIds` - IDs of the screens to move (must be from the same project)
     * `versionId` - ID of the new version
     *
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxMoveScreens()
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user    = Yii::$app->user->identity;
        $screens = $user->findScreensQuery($request->post('screenIds', -1))->all();
        $version = !empty($screens) ? $screens[0]->project->findVersionById($request->post('versionId', -1)) : null;

        if (!empty($screens) && $version) {
            $result = true;

            foreach ($screens as $screen) {
                $screen->versionId = $version->id;
                $result = $result && $screen->save();
            }

            if ($result) {
                return [
                    'success' => true,
                    'message' => Yii::t('app', 'Successfully moved screen(s).'),
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Helper that checks whether a hotspots array contains properly formatted data.
     * @param  string|array $hotspots
     * @return boolean
     */
    protected function checkHotspotsFormat($hotspots)
    {
        if ($hotspots === null) {
            return true;
        }

        $hotspots = is_array($hotspots) ? $hotspots : json_decode($hotspots, true);
        foreach ($hotspots as $hotspot) {
            if (!isset($hotspot['width']) || !isset($hotspot['height']) || !isset($hotspot['top']) || !isset($hotspot['left']) || !isset($hotspot['link'])) {
                return false;
            }
        }

        return true;
    }
}
