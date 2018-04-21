<?php
namespace common\components\validators;

use Yii;
use yii\validators\Validator;
use common\models\Screen;

/**
 * HotspotsValidator that validates screen hotspots.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class HotspotsValidator extends Validator
{
    /**
     * Array list with valid hotspot attributes.
     */
    const VALID_ATTRIBUTES = ['width', 'height', 'top', 'left', 'link', 'transition'];

    /**
     * Array list with required hotspot attributes.
     */
    const REQUIRED_ATTRIBUTES = ['width', 'height', 'top', 'left', 'link'];

    /**
     * Array list with numeric hotspot attributes.
     */
    const NUMERIC_ATTRIBUTES = ['width', 'height', 'top', 'left'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('app', 'Invalid hotspots format.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $valid            = true;
        $hotspots         = is_array($value) ? $value : ((array) json_decode($value, true));
        $validTransitions = array_keys(Screen::getTransitionLabels());

        foreach ($hotspots as $hotspot) {
            if (!is_array($hotspot)) {
                $valid = false;
                break;
            }

            // validate attributes schema
            foreach ($hotspot as $k => $v) {
                if (!in_array($k, self::VALID_ATTRIBUTES)) {
                    $valid = false;
                    break 2;
                }
            }

            // validate required attributes
            foreach (self::REQUIRED_ATTRIBUTES as $attr) {
                if (!isset($hotspot[$attr])) {
                    $valid = false;
                    break 2;
                }
            }

            // validate numeric attributes
            foreach (self::NUMERIC_ATTRIBUTES as $attr) {
                if (!isset($hotspot[$attr])) {
                    continue;
                }

                if (!is_numeric($hotspot[$attr])) {
                    $valid = false;
                    break 2;
                }
            }

            // validate transition attribute
            if (
                !empty($hotspot['transition']) &&
                !in_array($hotspot['transition'], $validTransitions)
            ) {
                $valid = false;
                break;
            }
        }

        return $valid ? null : [$this->message, []];
    }
}
