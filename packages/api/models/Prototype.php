<?php
namespace presentator\api\models;

use Yii;
use yii\helpers\Inflector;

/**
 * Prototype AR model
 *
 * @property integer $id
 * @property integer $projectId
 * @property string  $title
 * @property string  $type
 * @property float   $width
 * @property float   $height
 * @property float   $scaleFactor
 * @property string  $createdAt
 * @property string  $updatedAt
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Prototype extends ActiveRecord
{
    const TYPE = [
        'DESKTOP' => 'desktop',
        'MOBILE'  => 'mobile',
    ];

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::class, ['id' => 'projectId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getScreens()
    {
        return $this->hasMany(Screen::class, ['prototypeId' => 'id'])
            ->addOrderBy([Screen::tableName() . '.order' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHotspotTemplates()
    {
        return $this->hasMany(HotspotTemplate::class, ['prototypeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinkPrototypeRels()
    {
        return $this->hasMany(ProjectLinkPrototypeRel::class, ['prototypeId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProjectLinks()
    {
        return $this->hasMany(ProjectLink::class, ['id' => 'projectLinkId'])
            ->via('projectLinkPrototypeRels');
    }

    /**
     * @inheritdoc
     */
    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // delete project links that were restricted only for the deleted prototype
        foreach ($this->projectLinks as $projectLink) {
            if (
                count($projectLink->prototypes) == 1 &&
                $projectLink->prototypes[0]->id == $this->id &&
                !$projectLink->delete()
            ) {
                return false;
            }
        }

        // trigger related screens delete procedure
        foreach ($this->screens as $screen) {
            if (!$screen->delete()) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['screens']          = 'screens';
        $extraFields['hotspotTemplates'] = 'hotspotTemplates';

        return $extraFields;
    }

    /**
     * Duplicates the current prototype together with its screens, hotspot templates and hotspots.
     * On success returns the newly created prototype.
     *
     * @param  string [$title] Optional title of the duplicated prototype (default to counter).
     * @return presentator\api\models\Prototype
     * @throws \Exception | \Throwable
     */
    public function duplicate($title = ''): Prototype
    {
        $transaction = static::getDb()->beginTransaction();
        $newFiles = [];

        try {
            // duplicate prototype
            $prototypeCopy = clone $this;
            unset($prototypeCopy->id);
            $prototypeCopy->isNewRecord = true;
            $prototypeCopy->title = !empty($title) ? $title : ($prototype->title . ' (copy)');
            if (!$prototypeCopy->save()) {
                throw new \Exception('Unable to duplicate prototype ' . $this->id);
            }

            $hotspots = [];
            $screensMap = [];
            $templatesMap = [];

            // duplicate screens
            foreach ($this->getScreens()->with('hotspots')->each() as $screen) {
                $pathInfo = pathinfo($screen->filePath);

                $screenCopy = clone $screen;
                unset($screenCopy->id);
                $screenCopy->isNewRecord = true;
                $screenCopy->prototypeId = $prototypeCopy->id;
                $screenCopy->filePath = $pathInfo['dirname'] . '/' . (substr($pathInfo['filename'], 0, 88) . '_' . time()) . '.' . $pathInfo['extension'];
                if (!$screenCopy->save()) {
                    throw new \Exception('Unable to duplicate screen ' . $screen->id);
                }

                // copy the file itself
                if (!Yii::$app->fs->copy($screen->filePath, $screenCopy->filePath)) {
                    throw new \Exception('Unable to copy screen file ' . $screen->filePath);
                }
                $newFiles[] = $screenCopy->filePath; // keep track of the created files

                $screensMap[$screen->id] = $screenCopy;

                $hotspots = array_merge($hotspots, $screen->hotspots);
            }

            // duplicate hotspot templates
            foreach ($this->getHotspotTemplates()->with(['hotspots', 'hotspotTemplateScreenRels'])->each() as $template) {
                $templateCopy = clone $template;
                unset($templateCopy->id);
                $templateCopy->isNewRecord = true;
                $templateCopy->prototypeId = $prototypeCopy->id;
                if (!$templateCopy->save()) {
                    throw new \Exception('Unable to duplicate hotspot template ' . $template->id);
                }

                // duplicate template-screen rels
                foreach ($template->hotspotTemplateScreenRels as $screenRel) {
                    if (!empty($screensMap[$screenRel->screenId])) {
                        $templateCopy->linkOnce('screens', $screensMap[$screenRel->screenId]);
                    }
                }

                $templatesMap[$template->id] = $templateCopy;

                $hotspots = array_merge($hotspots, $template->hotspots);
            }

            // duplicate screen hotspots
            foreach ($hotspots as $hotspot) {
                if (
                    (!empty($hotspot->screenId) && empty($screensMap[$hotspot->screenId])) ||
                    (!empty($hotspot->hotspotTemplateId) && empty($templatesMap[$hotspot->hotspotTemplateId]))
                ) {
                    continue; // missing mapping relation
                }

                $settings = $hotspot->getDecodedSettings();
                if (
                    !empty($settings[Hotspot::SETTING['SCREEN']]) &&
                    !empty($screensMap[$settings[Hotspot::SETTING['SCREEN']]])
                ) {
                    $settings[Hotspot::SETTING['SCREEN']] = $screensMap[$settings[Hotspot::SETTING['SCREEN']]]->id;
                }

                $hotspotCopy = clone $hotspot;
                unset($hotspotCopy->id);
                $hotspotCopy->isNewRecord = true;
                if (!empty($screensMap[$hotspot->screenId])) {
                    $hotspotCopy->screenId = $screensMap[$hotspot->screenId]->id;
                }
                if (!empty($templatesMap[$hotspot->hotspotTemplateId])) {
                    $hotspotCopy->hotspotTemplateId = $templatesMap[$hotspot->hotspotTemplateId]->id;
                }
                if (!$hotspotCopy->save()) {
                    throw new \Exception('Unable to duplicate hotspot ' . $hotspot->id);
                }
            }

            $transaction->commit();

            return $prototypeCopy;
        } catch(\Exception | \Throwable $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());

            // cleanup newly created files
            foreach ($newFiles as $file) {
                Yii::$app->fs->delete($file);
            }

            // rethrow
            throw $e;
        }
    }
}
