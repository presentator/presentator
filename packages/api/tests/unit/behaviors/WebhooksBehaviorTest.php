<?php
namespace presentator\api\tests\unit\behaviors;

use Yii;
use presentator\api\base\WebhookTransformer;
use presentator\api\tests\mocks\WebhookTransformerMock;
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
     * WebooksBehavior ActiveRecord integration test with simple url hooks.
     */
    public function testActiveRecordIntegration()
    {
        $testScenarios = [
            // no definitions options
            [],
            // url as create hook
            [
                'create' => 'http://create-hook1',
            ],
            // url as update hook
            [
                'update' => 'http://update-hook',
            ],
            // url as delete hook
            [
                'delete' => 'http://delete-hook',
            ],
            // url for create, update and delete hooks + custom fields and expand
            [
                'create' => ['http://create-hook1', 'http://create-hook2'],
                'update' => 'http://update-hook',
                'delete' => 'http://delete-hook',
                'fields' => ['id'],
                'expand' => ['prototypes'],
            ],
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
                $createHooks = isset($scenario['create']) ? (array) $scenario['create'] : [];
                $updateHooks = isset($scenario['update']) ? (array) $scenario['update'] : [];
                $deleteHooks = isset($scenario['delete']) ? (array) $scenario['delete'] : [];
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

    /**
     * WebooksBehavior test with advanced WebhookTransformer hooks.
     */
    public function testWebhookTransformer()
    {
        $this->specify('with WebhookTransformer hooks', function() {
            $clientDouble = test::double(Client::class, ['request' => null]);

            Yii::$app->set('webhooks', Yii::createObject([
                'class' => 'presentator\api\base\Webhooks',
                'definitions' => [
                    Project::class => [
                        'create' => ['class'=> WebhookTransformerMock::class, 'testData' => []],
                        'update' => ['class'=> WebhookTransformerMock::class, 'testUrl' => ''],
                        'delete' => ['class'=> WebhookTransformerMock::class],
                    ],
                ],
            ]));

            // update (empty data)
            $dummy = new Project(['title' => 'test']);
            verify('Should create the dummy model successfully and trigger create event', $dummy->save())->true();
            $clientDouble->verifyNeverInvoked('request');

            // update (empty url)
            $dummy->title = 'new_test';
            verify('Should update the dummy model successfully and trigger update event', $dummy->save())->true();
            $clientDouble->verifyNeverInvoked('request');

            // delete (nonempty url and data)
            verify('Should delete the dummy model successfully and trigger delete event', $dummy->delete())->equals(1);
            $clientDouble->verifyInvokedOnce('request', ['POST', 'test_url', ['json' => ['test_key' => 'test_value'], 'timeout' => 1]]);
        });
    }
}
