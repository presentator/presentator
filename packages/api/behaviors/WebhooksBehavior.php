<?php
namespace presentator\api\behaviors;

use Yii;
use yii\base\Event;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use GuzzleHttp\Client;
use presentator\api\base\WebhookTransformer;

/**
 * Adds basic webhooks support to an AR model.
 *
 * Example usage:
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         'webhooks' => [
 *             'class' => WebhooksBehavior::class,
 *             'create' => [
 *                 'https://my-hook1.com',
 *                 'https://my-hook2.com',
 *             ],
 *             'update' => ['class' => '\custom\hook\transformer\class'],
 *             'delete' => 'https://my-hook3.com',
 *         ],
 *         ...
 *     ];
 * }
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class WebhooksBehavior extends Behavior
{
    /**
     * After model create webhook.
     *
     * It could be either simple url address(es) or/and `WebhookTransformer`
     * class instance for the more advanced cases.
     *
     * @var null|string|array
     */
    public $create;

    /**
     * After model update webhook.
     *
     * It could be either simple url address(es) or/and `WebhookTransformer`
     * class instance for the more advanced cases.
     *
     * @var null|string|array
     */
    public $update;

    /**
     * Before model delete webhook.
     *
     * It could be either simple url address(es) or/and `WebhookTransformer`
     * class instance for the more advanced cases.
     *
     * @var null|string|array
     */
    public $delete;

    /**
     * List of model fields that the webhook data should contain.
     * If this parameter is empty, all fields as specified in the model's `fields()` will be returned.
     * This property is used only with the simple url type webhooks.
     *
     * @var array
     */
    public $fields = [];

    /**
     * Additional fields from the model `extraFields()` that the webhook data should contain.
     * If this parameter is empty, no extra fields will be returned.
     * This property is used only with the simple url type webhooks.
     *
     * @var array
     */
    public $expand = [];

    /**
     * @inheritdoc
     */
    public function events()
    {
        $events = parent::events();

        $events[ActiveRecord::EVENT_AFTER_INSERT]  = 'afterInsert';
        $events[ActiveRecord::EVENT_AFTER_UPDATE]  = 'afterUpdate';
        $events[ActiveRecord::EVENT_BEFORE_DELETE] = 'beforeDelete';

        return $events;
    }

    /**
     * @param yii\base\Event $event
     */
    public function afterInsert($event)
    {
        $this->send($this->create, $event);
    }

    /**
     * @param yii\base\Event $event
     */
    public function afterUpdate($event)
    {
        $this->send($this->update, $event);
    }

    /**
     * @param yii\base\Event $event
     */
    public function beforeDelete($event)
    {
        $this->send($this->delete, $event);
    }

    /**
     * POST `$event` to the provided hook(s).
     *
     * @param  string|array $hooks
     * @param  Event        $event
     */
    protected function send($hooks, Event $event)
    {
        if (empty($hooks)) {
            return; // no hook(s)
        }

        // normalize
        $hooks = is_array($hooks) && isset($hooks[0]) ? $hooks : [$hooks];

        // ensures that all model fields are up-to-date
        $event->sender->refresh();

        $client = new Client();

        foreach ($hooks as $hook) {
            try {
                if (is_string($hook)) {
                    $url = $hook;
                    $data = [
                        'event' => $event->name,
                        'model' => StringHelper::basename(get_class($event->sender)),
                        'data'  => $event->sender->toArray($this->fields, $this->expand),
                    ];
                } else {
                    $transformer = Yii::createObject($hook);
                    if (!($transformer instanceof WebhookTransformer)) {
                        continue;
                    }

                    $url = $transformer->getUrl($event);
                    $data = $transformer->getData($event);
                }

                if (empty($url) || empty($data)) {
                    continue;
                }

                $client->request('POST', $url, [
                    'json' => $data,
                    'timeout' => 1, // we don't need the response so there is no needed to wait too longer for it
                ]);
            } catch (\Exception | \Throwable $e) {
                Yii::error($e->getMessage());
            }
        }
    }
}
