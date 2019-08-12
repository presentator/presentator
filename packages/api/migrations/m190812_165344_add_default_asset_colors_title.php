<?php
use yii\db\Migration;
use presentator\api\models\GuidelineSection;
use presentator\api\models\GuidelineAsset;

/**
 * Sets default titles for guideline asset colors.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class m190812_165344_add_default_asset_colors_title extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        foreach (GuidelineSection::find()->each() as $section) {
            $count       = 0;
            $assetsQuery = $section->getAssets()
                ->andWhere([
                    GuidelineAsset::tableName() . '.type'  => GuidelineAsset::TYPE['COLOR'],
                    GuidelineAsset::tableName() . '.title' => '',
                ]);

            foreach ($assetsQuery->each() as $asset) {
                $asset->title = 'Color ' . ++$count;
                $asset->save();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }
}
