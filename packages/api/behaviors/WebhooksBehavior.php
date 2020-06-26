<?php
namespace presentator\api\behaviors;

use Yii;
use yii\base\Event;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\StringHelper;
use GuzzleHttp\Client;

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
 *             'expand' => ['settings'],
 *             'createUrl' => [
 *                 'http://my-create-hook1.com',
 *                 'http://my-create-hook2.com',
 *             ],
 *             'updateUrl' => 'http://my-create-hook2.com',
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
     * Url address(es) to send POST data to after model create.
     *
     * @var null|string|array
     */
    public $createUrl;

    /**
     * Url address(es) to send POST data to after model update.
     *
     * @var null|string|array
     */
    public $updateUrl;

    /**
     * Url address(es) to send POST data to before model delete.
     *
     * @var null|string|array
     */
    public $deleteUrl;

    /**
     * List of model fields that the webhook data should contain.
     * If this parameter is empty, all fields as specified in the model's `fields()` will be returned.
     *
     * @var array
     */
    public $fields = [];

    /**
     * Additional fields from the model `extraFields()` that the webhook data should contain.
     * If this parameter is empty, no extra fields will be returned.
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
        $this->send($this->createUrl, $event);
    }

    /**
     * @param yii\base\Event $event
     */
    public function afterUpdate($event)
    {
        $this->send($this->updateUrl, $event);
    }

    /**
     * @param yii\base\Event $event
     */
    public function beforeDelete($event)
    {
        $this->send($this->deleteUrl, $event);
    }

    /**
     * POST `$event` data as json to the provided url(s).
     *
     * @param  string|array $urls
     * @param  Event        $event
     */
    protected function send($urls, Event $event) {
        if (empty($urls)) {
            return; // no hook url(s)
        }

        $urls = is_array($urls) ? $urls : [$urls];

        $data = [
            'event' => $event->name,
            'model' => StringHelper::basename(get_class($event->sender)),
            'data'  => $event->sender->toArray($this->fields, $this->expand),
        ];

        $client = new Client();

        foreach ($urls as $url) {
            try {
                $client->request('POST', $url, [
                    'json' => $data,
                    'timeout' => 1, // we don't need the response so there is no needed to wait too longer for it
                ]);
            } catch (\Exception | \Throwable $e) {
                // silence timeout exceptions
            }
        }
    }
}
