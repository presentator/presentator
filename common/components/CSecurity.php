<?php
namespace common\components;

use yii\base\Security;
use yii\base\InvalidParamException;

/**
 * Custom Securty component class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CSecurity extends Security
{
    /**
     * Generates random string with option to specify an alphabet.
     * @example
     * Yii::$app->security->generateRandomString(8, [
     *     ['abcdefghijklmnopqrstuvwxyz', 6] // min 6 characters
     *     ['123456', 1] // min 1 character
     *     ['^$?>!'] // min 1 character
     * ])
     * @param  integer $length
     * @param  array   $alphabets
     * @return string
     * @throws InvalidParamException if min alphabet length is bigger than the str length
     */
    public function generateRandomString($length = 32, array $alphabets = [])
    {
        if (empty($alphabets)) {
            return parent::generateRandomString($length);
        }

        $usedLength = 0;

        $resultString = '';
        $concatenatedAlphabet = '';
        foreach ($alphabets as $alphabet) {
            $alphabetChars    = $alphabet[0];
            $alphabetMinCount = isset($alphabet[1]) ? (int) $alphabet[1] : 1;

            for ($i = 0; $i < $alphabetMinCount; $i++) {
                $resultString .= $alphabetChars[rand(0, strlen($alphabetChars)-1)];
            }

            $concatenatedAlphabet .= $alphabetChars;
        }

        $lengthDiff = $length - strlen($resultString);
        if ($lengthDiff < 0) {
            throw new InvalidParamException('The sum of alphabets min length should not be bigger than the desired string length!');
        } elseif ($lengthDiff > 0) {
            for ($i = 0; $i < $lengthDiff; $i++) {
                $resultString .= $concatenatedAlphabet[rand(0, strlen($concatenatedAlphabet)-1)];
            }
        }

        // shuffle and return the string
        return str_shuffle($resultString);
    }
}
