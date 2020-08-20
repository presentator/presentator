<?php
namespace presentator\api\tests\mocks;

use yii\base\Event;
use presentator\api\base\WebhookTransformer;

/**
 * WebhookTransformer mock class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class WebhookTransformerMock extends WebhookTransformer
{
    public String $testUrl = 'test_url';

    public array $testData = ['test_key' => 'test_value'];

    /**
     * @inheritdoc
     */
    public function getUrl(Event $event): string {
        return $this->testUrl;
    }

    /**
     * @inheritdoc
     */
    public function getData(Event $event): array {
        return $this->testData;
    }
}
