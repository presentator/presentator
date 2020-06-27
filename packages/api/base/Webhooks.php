<?php
namespace presentator\api\base;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * Webhooks is a storage for all webhooks configurations in the application.
 * Usually used together with `presentator\api\behaviors\WebhooksBehavior`.
 *
 * Example usage:
 * ```php
 * 'components' => [
 *     'webhooks' => [
 *         'class' => 'presentator\api\base\Webhooks',
 *         'definitions' => [
 *             'presentator\api\models\User' => [
 *                 'expand' => ['settings'],
 *                 'createUrl' => [
 *                     'http://my-create-hook1.com',
 *                     'http://my-create-hook2.com',
 *                 ],
 *                 'updateUrl' => 'http://my-create-hook2.com',
 *             ],
 *         ],
 *     ]
 *     ...
 * ]
 * ```
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Webhooks extends Component
{
    /**
     * List of hook definitions.
     * Must be in the following format: 'MODEL_NAMESPACE' => [...WebhooksBehavior options...].
     *
     * @var array
     */
    public $definitions = [];

    /**
     * Returns the webhooks definition for the provided fully qualified model class name.
     *
     * @param string $modelClass
     * @return array
     */
    public function getDefinition(string $modelClass)
    {
        if (!empty($this->definitions[$modelClass]) && is_array($this->definitions[$modelClass])) {
            return $this->definitions[$modelClass];
        }

        return [];
    }
}
