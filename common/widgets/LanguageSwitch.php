<?php
namespace common\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Url;
use yii\helpers\Html;

/**
 * LanguageSwitch widget that renders a language select box.
 *
 * @example
 * // simple
 * <?= LanguageSwitch::widget(); ?>
 *
 * // advanced
 * <?= LanguageSwitch::widget([
 *     'options' => [
 *         'class' => 'myCustomClass'
 *     ]
 * ]) ?>
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class LanguageSwitch extends Widget
{
    /**
     * Enables you to define custom attributes to the wrapper container tag.
     *
     * @example
     * $options = [
     *     'class' => 'no-radius-b-l no-radius-b-r',
     *     'data-cursor-tooltup' => 'Lorem ipsum dolor sit amet',
     * ];
     *
     * @var array
     */
    public $options = [];

    /**
     * @inheritdoc
     */
    public function run()
    {
        $currentLang = strtolower(Yii::$app->language);

        $customClasses = isset($this->options['class']) ? ' ' . $this->options['class'] : '';
        unset($this->options['class']);
        $tagAttributes = Html::renderTagAttributes($this->options);

        $result = '<div class="language-widget ' . $customClasses .'" ' . $tagAttributes . '>';
        $result .= '<label for="language_select">' . Yii::t('app', 'Language') . '</label>';
        $result .= '<select id="language_select" class="language-select">';

        // language options
        $result .= sprintf('<option value="%s" %s>%s</option>',
            Url::current(['lang' => 'bg']),
            ($currentLang == 'bg-bg' ? 'selected' : ''),
            (Yii::t('app', 'Bulgarian'))
        );
        $result .= sprintf('<option value="%s" %s>%s</option>',
            Url::current(['lang' => 'en']),
            ($currentLang == 'en-us' ? 'selected' : ''),
            (Yii::t('app', 'English'))
        );
        $result .= sprintf('<option value="%s" %s>%s</option>',
            Url::current(['lang' => 'pl']),
            ($currentLang == 'pl-pl' ? 'selected' : ''),
            (Yii::t('app', 'Polish'))
        );
        // $result .= sprintf('<option value="%s" %s>%s</option>',
        //     Url::current(['lang' => 'pt-br']),
        //     ($currentLang == 'pt-br' ? 'selected' : ''),
        //     (Yii::t('app', 'Portuguese (Brazilian)'))
        // );

        $result .= '</select>';
        $result .= '</div>';

        return $result;
    }
}
