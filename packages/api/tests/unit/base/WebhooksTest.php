<?php
namespace presentator\api\tests\unit\base;

use presentator\api\base\Webhooks;

/**
 * Webhooks component tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class WebhooksTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * `Webhooks::getDefinition()` method test.
     */
    public function testGetDefinition()
    {
        $webhooks = new Webhooks(['definitions' => ['classA' => ['a' => 1]]]);

        verify('Webhook definition should not be empty', $webhooks->getDefinition('classA'))->equals(['a' => 1]);
        verify('Webhook definition should be empty', $webhooks->getDefinition('classB'))->equals([]);
    }
}
