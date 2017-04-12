<?php
namespace common\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * FlashAlert widget that renders a simple session flash message.
 *
 * @example
 * // simple
 * <?= FlashAlert::widget(); ?>
 *
 * // advanced
 * <?= FlashAlert::widget([
 *     'options' => [
 *         'class' => 'myCustomClass'
 *     ],
 *     'types' => [
 *         'custom_type1' => 'class1',
 *         'custom_type2' => 'class2',
 *     ]
 * ]) ?>
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class FlashAlert extends Widget
{
    /**
     * Enables you to define custom attributes to the alert container tag.
     *
     * @example
     * $options = [
     *     'class' => 'no-radius-b-l no-radius-b-r',
     *     'data-target' => '#myTarget',
     * ];
     *
     * @var array
     */
    public $options = [];

    /**
     * Sets custom content that will be outputted right before the alert(s).
     * @var string
     */
    public $before = '';

    /**
     * Sets custom content that will be outputted right after the alert(s).
     * @var string
     */
    public $after = '';

    /**
     * Whether to add alert close handle or not.
     * @var boolean
     */
    public $close = true;

    /**
     * key=>value pair of flash message configurations, where:
     * - `key`   is the flash message type
     * - `value` is the corresponding css alert class
     * @var array
     */
    public $types = [
        'info'    => 'alert-default',
        'error'   => 'alert-danger',
        'success' => 'alert-success',
        'warning' => 'alert-warning',
    ];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes(false);

        $customClasses = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
        unset($this->options['class']);
        $tagAttributes = Html::renderTagAttributes($this->options);

        $result = '';
        foreach ($flashes as $type => $data) {
            if (isset($this->types[$type])) {
                $result .= $this->before;
                $result .= sprintf('<div class="alert %s%s" %s>', $this->types[$type], $customClasses, $tagAttributes);

                $data = (array) $data;
                foreach ($data as $item) {
                    $result .= $item;
                }

                if ($this->close) {
                    $result .= '<span class="close"><i class="ion ion-close"></i></span>';
                }

                $result .= '</div>';
                $result .= $this->after;
            }
        }

        return $result;
    }
}
