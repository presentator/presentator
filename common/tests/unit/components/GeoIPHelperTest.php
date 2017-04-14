<?php
namespace common\tests\unit\components;

use Yii;
use common\components\helpers\GeoIPHelper;

/**
 * GeoIPHelper tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class GeoIPHelperTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `GeoIPHelper::detectLanguageCode()` method test.
     */
    public function testDetectLanguageCode()
    {
        $lang = GeoIPHelper::detectLanguageCode();
        verify('Should return valid language key code', Yii::$app->params['languages'])->hasKey($lang);
    }
}
