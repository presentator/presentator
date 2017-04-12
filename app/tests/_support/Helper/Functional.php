<?php
namespace app\tests\Helper;

use Yii;

/**
 * Functional tests helper.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Functional extends \Codeception\Module
{
    /**
     * Sends a POST request to given uri.
     * Will automatically add the csrf param to the request.
     *
     * @param string|array $url
     * @param array        $params
     * @param array        $files
     * @param array        $server
     */
    public function sendPOST($url, array $params = [], array $files = [], array $server = [])
    {
        if (is_array($url)) {
            $url = Yii::$app->urlManager->createUrl($url);
        }

        // add the csrf param to the request
        $params[Yii::$app->request->csrfParam] = Yii::$app->request->getCsrfToken();

        $this->getModule('Yii2')->_request('POST', $url, $params, $files, $server);
    }

    /**
     * Tests whether a flash message is set.
     * @param string $key
     */
    public function seeFlash($key)
    {
        return $this->assertTrue(
            Yii::$app->session->hasFlash($key),
            'Flash mesage "' . $key . '" should be set'
        );
    }

    /**
     * Tests whether a flash message is not set.
     * @param string $key
     */
    public function dontSeeFlash($key)
    {
        return $this->assertFalse(
            Yii::$app->session->hasFlash($key),
            'Flash mesage "' . $key . '" should not be set'
        );
    }

    /**
     * Tests whether a response contains specific string.
     * @param string $text
     */
    public function seeResponseContains($text)
    {
        $this->assertContains($text, $this->getModule('Yii2')->_getResponseContent(), "response contains");
    }

    /**
     * Tests whether a response contains specific string.
     * @param string $text
     */
    public function dontSeeResponseContains($text)
    {
        $this->assertNotContains($text, $this->getModule('Yii2')->_getResponseContent(), "response doesn't contains");
    }

    /**
     * Tests if AR model records count is changed.
     * @param string  $modelClass
     * @param integer $oldCount
     * @param integer $changedRecords
     */
    public function seeRecordsCountChange($modelClass, $oldCount = 0, $changedRecords = 1)
    {
        $this->assertEquals($oldCount + $changedRecords, $modelClass::find()->count());
    }

    /**
     * Tests if AR model records count is not changed.
     * @param string  $modelClass
     * @param integer $oldCount
     */
    public function dontSeeRecordsCountChange($modelClass, $oldCount = 0)
    {
       $this->assertEquals($oldCount, $modelClass::find()->count());
    }
}
