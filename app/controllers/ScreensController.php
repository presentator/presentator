<?php
namespace app\controllers;

use Yii;
use yii\web\Response;
use yii\web\BadRequestHttpException;
use common\components\web\CUploadedFile;
use common\components\helpers\CArrayHelper;
use common\models\Screen;
use app\models\ScreensUploadForm;
use app\models\ScreenReplaceForm;
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
            'ajax-replace'            => ['post'],
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

                    $listItemsHtml .= $this->renderPartial('/screens/_list_item', [
                        'model'       => $screen,
                        'lazyLoad'    => false,
                        'createThumb' => false,
                    ]);
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
     * Replace single screen image via ajax.
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxReplace()
    {
        if (!Yii::$app->request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user     = Yii::$app->user->identity;
        $screenId = Yii::$app->request->post('screenId', -1);
        $screen   = $user->findScreenById($screenId);

        if ($screen) {
            $replaceForm        = new ScreenReplaceForm($screen);
            $replaceForm->image = CUploadedFile::getInstance($replaceForm, 'image');

            if ($screens = $replaceForm->save()) {
                $screen->refresh();

                return [
                    'success' => true,
                    'screen'  => $screen->toArray(),
                ];
            }

            return [
                'success' => false,
                'message' => implode('<br/>', $replaceForm->getFirstErrors()),
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
                if (count($screens) > 1) {
                    $message = Yii::t('app', 'Successfully deleted screens.');
                } else {
                    $message = Yii::t('app', 'Successfully deleted screen.');
                }

                return [
                    'success' => true,
                    'message' => $message,
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Returns and renders screen settings popup content via ajax.
     * @param  integer $id
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxGetSettings($id)
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
                'success'      => true,
                'settingsHtml' => $this->render('/screens/_settings', ['model' => $model]),
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
            $changesInfo = $this->detectHotspotsChanges($screen->hotspots, $hotspots);

            // add response message on specific hotspots change
            $message = '';
            if ($changesInfo['linksUpdate'] || $changesInfo['transitionsUpdate']) {
                $message = Yii::t('app', 'Successfully saved changes.');
            }

            $screen->hotspots = $hotspots;

            if ($screen->save()) {
                return [
                    'success' => true,
                    'message' => $message,
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
                if (count($screens) > 1) {
                    $message = Yii::t('app', 'Successfully moved screens.');
                } else {
                    $message = Yii::t('app', 'Successfully moved screen.');
                }

                return [
                    'success' => true,
                    'message' => $message,
                ];
            }
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Fetch and generate screen thumb via ajax.
     * @param  integer     $id
     * @param  null|string $thumbSize
     * @return array
     * @throws BadRequestHttpException For none ajax request
     */
    public function actionAjaxGetThumbs($id, $thumbSize = null)
    {
        $request = Yii::$app->request;
        if (!$request->isAjax) {
            throw new BadRequestHttpException('Error Processing Request');
        }

        Yii::$app->response->format = Response::FORMAT_JSON;

        $user   = Yii::$app->user->identity;
        $screen = $user->findScreenById($id);
        $sizes  = array_key_exists($thumbSize, Screen::THUMB_SIZES) ? [$thumbSize] : array_keys(Screen::THUMB_SIZES);

        if ($screen) {
            $thumbs = [];
            foreach ($sizes as $size) {
                $thumbs[$size] = $screen->getThumbUrl($size, true);
            }

            return [
                'success' => true,
                'thumbs'  => $thumbs,
            ];
        }

        return [
            'success' => false,
            'message' => Yii::t('app', 'Oops, an error occurred while processing your request.'),
        ];
    }

    /**
     * Helper that checks whether a hotspots array contains properly formatted data.
     * @param  null|string|array $hotspots
     * @return boolean
     */
    protected function checkHotspotsFormat($hotspots)
    {
        if ($hotspots === null) {
            return true;
        }

        $hotspots           = is_array($hotspots) ? $hotspots : json_decode($hotspots, true);
        $validTransitions   = array_keys(Screen::getTransitionLabels());
        $requiredAttributes = ['width', 'height', 'top', 'left', 'link'];

        foreach ($hotspots as $hotspot) {
            foreach ($requiredAttributes as $attr) {
                if (!isset($hotspot[$attr])) {
                    return false;
                }
            }

            if (!empty($hotspot['transition']) && !in_array($hotspot['transition'], $validTransitions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns various info for hotspots changes.
     * @param  null|string|array  $oldHotspots
     * @param  null|string|array  $newHotspots
     * @return array
     */
    protected function detectHotspotsChanges($oldHotspots, $newHotspots)
    {
        $oldHotspots = is_array($oldHotspots) ? $oldHotspots : ((array) json_decode($oldHotspots, true));
        $newHotspots = is_array($newHotspots) ? $newHotspots : ((array) json_decode($newHotspots, true));

        $created           = 0;
        $deleted           = 0;
        $positionsUpdate   = 0;
        $sizesUpdate       = 0;
        $linksUpdate       = 0;
        $transitionsUpdate = 0;

        foreach ($oldHotspots as $id => $props) {
            if (!isset($newHotspots[$id])) {
                $deleted++;
                continue;
            }
            if (
                CArrayHelper::getValue($props, 'left') != CArrayHelper::getValue($newHotspots[$id], 'left') ||
                CArrayHelper::getValue($props, 'top') != CArrayHelper::getValue($newHotspots[$id], 'top')
            ) {
                $positionsUpdate++;
            }

            if (
                CArrayHelper::getValue($props, 'width') != CArrayHelper::getValue($newHotspots[$id], 'width') ||
                CArrayHelper::getValue($props, 'height') != CArrayHelper::getValue($newHotspots[$id], 'height')
            ) {
                $sizesUpdate++;
            }

            if (CArrayHelper::getValue($props, 'link') != CArrayHelper::getValue($newHotspots[$id], 'link')) {
                $linksUpdate++;
            }

            if (CArrayHelper::getValue($props, 'transition') != CArrayHelper::getValue($newHotspots[$id], 'transition')) {
                $transitionsUpdate++;
            }
        }

        $created = count($newHotspots) - (count($oldHotspots) - $deleted);

        return [
            'created'           => $created,
            'deleted'           => $deleted,
            'positionsUpdate'   => $positionsUpdate,
            'sizesUpdate'       => $sizesUpdate,
            'linksUpdate'       => $linksUpdate,
            'transitionsUpdate' => $transitionsUpdate,
        ];
    }
}
