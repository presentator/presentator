<?php
namespace presentator\api\tests\unit\behaviors;

use Yii;
use presentator\api\behaviors\WebhooksBehavior;
use presentator\api\tests\fixtures\ProjectFixture;
use presentator\api\tests\fixtures\PrototypeFixture;
use presentator\api\models\Project;
use GuzzleHttp\Client;
use AspectMock\Test as test;

/**
 * WebhooksBehavior tests.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class WebhooksBehaviorTest extends \Codeception\Test\Unit
{
    use \Codeception\Specify;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    /**
     * @inherit
     */
    protected function _before()
    {
        $this->tester->haveFixtures([
            'ProjectFixture' => [
                'class' => ProjectFixture::class,
            ],
            'PrototypeFixture' => [
                'class' => PrototypeFixture::class,
            ],
        ]);
    }

    /**
     * @inherit
     */
    protected function _after()
    {
        test::clean();
    }

    /**
     * WebooksBehavior ActiveRecord integration test.
     */
    public function testActiveRecordIntegration()
    {
        $testScenarios = [
            [ /* no definition options */ ],
            [
                // just create hook
                'createUrl' => 'http://create-hook1',
            ],
            [
                // just update hook
                'updateUrl' => 'http://update-hook',
            ],
            [
                // just delete hook
                'deleteUrl' => 'http://delete-hook',
            ],
            [
                // all hooks + custom fields and expand
                'createUrl' => ['http://create-hook1', 'http://create-hook2'],
                'updateUrl' => 'http://update-hook',
                'deleteUrl' => 'http://delete-hook',
                'fields'    => ['id'],
                'expand'    => ['prototypes'],
            ]
        ];

        foreach ($testScenarios as $index => $scenario) {
            $this->specify('Webhooks AR integration scenario ' . $index, function() use ($scenario) {
                Yii::$app->set('webhooks', Yii::createObject([
                    'class' => 'presentator\api\base\Webhooks',
                    'definitions' => [
                        Project::class => $scenario,
                    ],
                ]));

                $clientDouble = test::double(Client::class, ['request' => null]);

                // normalize scenario fields
                $createHooks = isset($scenario['createUrl']) ? (array) $scenario['createUrl'] : [];
                $updateHooks = isset($scenario['updateUrl']) ? (array) $scenario['updateUrl'] : [];
                $deleteHooks = isset($scenario['deleteUrl']) ? (array) $scenario['deleteUrl'] : [];
                $fields      = isset($scenario['fields']) ? $scenario['fields'] : [];
                $expand      = isset($scenario['expand']) ? $scenario['expand'] : [];

                $expectedRequestCalls = count($createHooks) + count($updateHooks) + count($deleteHooks);

                $dummy = new Project(['title' => 'test']);
                verify('Should create the dummy model successfully and trigger create event', $dummy->save())->true();
                $dummy->refresh();
                $createHookData = [
                    'event' => 'afterInsert',
                    'model' => 'Project',
                    'data'  => $dummy->toArray($fields, $expand),
                ];

                $dummy->title = 'new_test';
                verify('Should update the dummy model successfully and trigger update event', $dummy->save())->true();
                $dummy->refresh();
                $updateHookData = [
                    'event' => 'afterUpdate',
                    'model' => 'Project',
                    'data'  => $dummy->toArray($fields, $expand),
                ];

                $deleteHookData = [
                    'event' => 'beforeDelete',
                    'model' => 'Project',
                    'data'  => $dummy->toArray($fields, $expand),
                ];
                verify('Should delete the dummy model successfully and trigger delete event', $dummy->delete())->equals(1);

                // count processed hooks
                if ($expectedRequestCalls > 0) {
                    $clientDouble->verifyInvokedMultipleTimes('request', $expectedRequestCalls);
                } else {
                    $clientDouble->verifyNeverInvoked('request');
                }

                // check sent data
                foreach ($createHooks as $hook) {
                    $clientDouble->verifyInvokedOnce('request', ['POST', $hook, ['json' => $createHookData, 'timeout' => 1]]);
                }
                foreach ($updateHooks as $hook) {
                    $clientDouble->verifyInvokedOnce('request', ['POST', $hook, ['json' => $updateHookData, 'timeout' => 1]]);
                }
                foreach ($deleteHooks as $hook) {
                    $clientDouble->verifyInvokedOnce('request', ['POST', $hook, ['json' => $deleteHookData, 'timeout' => 1]]);
                }

                test::clean();
            });
        }
    }
}
