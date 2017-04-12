<?php
namespace common\tests\unit\components;

use Yii;
use common\components\helpers\CFileHelper;

/**
 * CFileHelper tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CFileHelperTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * Testes whether `CFileHelper::getUrlFromPath` returns valid
     * public url (relative/absolute) from public path string.
     */
    public function testGetUrlFromPath()
    {
        $path = Yii::getAlias('@mainWeb') . '/test.txt';

        $this->specify('Absolute url', function() use ($path) {
            $url = CFileHelper::getUrlFromPath($path, true);
            verify('Is absolute url', $url)->startsWith(Yii::$app->params['publicUrl']);
            verify('Is correctly formed', $url)->endsWith('/test.txt');
        });

        $this->specify('Relative url', function() use ($path) {
            $url = CFileHelper::getUrlFromPath($path, false);
            verify('Is relative url', $url)->notStartsWith(Yii::$app->params['publicUrl']);
            verify('Is correctly formed', $url)->endsWith('/test.txt');
        });
    }

    /**
     * Testes whether `CFileHelper::getPathFromUrl` returns valid
     * public path from an url string (relative/absolute).
     */
    public function testGetPathFromUrl()
    {
        $this->specify('Path from absolute url', function() {
            $url  = Yii::$app->params['publicUrl'] . '/test';
            $path = CFileHelper::getPathFromUrl($url);
            verify('Is valid public path', $path)->startsWith(Yii::getAlias('@mainWeb'));
            verify('Is correctly formed', $path)->endsWith('/test');
        });

        $this->specify('Relative url', function() {
            $url  = '/test';
            $path = CFileHelper::getPathFromUrl($url);
            verify('Is valid public path', $path)->startsWith(Yii::getAlias('@mainWeb'));
            verify('Is correctly formed', $path)->endsWith('/test');
        });
    }
}
