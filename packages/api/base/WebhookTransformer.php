<?php
namespace presentator\api\base;

use yii\base\Event;
use yii\base\Component;

/**
 * Abstract Webhook definition transformer class for the advanced cases
 * when you need to:
 * - create dynamic Webhook endpoint based on the Webhook data
 * - filter and format the Webhook data before sending it
 *
 * See also `presentator\api\base\Webhooks` and `presentator\api\behaviors\WebhooksBehavior`.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
abstract class WebhookTransformer extends Component
{
    /**
     * The Webhook definition endpoint.
     * If you want to skip processing the Webhook, return an empty string.
     *
     * The following `$event` properties are commonly used:
     * - `$event->name`   - the name of the event action (eg. 'afterInsert')
     * - `$event->sender` - the ActiveRecord event model
     *
     * @param  Event $event
     * @return string
     */
    abstract public function getUrl(Event $event): string;

    /**
     * The data to send to the Webhook endpoint.
     * If you want to skip processing the Webhook, return an empty array.
     *
     * The following `$event` properties are commonly used:
     * - `$event->name`   - the name of the event action (eg. 'afterInsert')
     * - `$event->sender` - the ActiveRecord event model
     *
     * @param  Event $event
     * @return array
     */
    abstract public function getData(Event $event): array;
}
