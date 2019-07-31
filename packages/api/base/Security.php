<?php
namespace presentator\api\base;

use yii\base\InvalidParamException;

/**
 * Custom Securty component class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class Security extends \yii\base\Security
{
    /**
     * Generates random string with option to specify an alphabet.
     *
     * Example usage:
     * ```php
     * Yii::$app->security->generateRandomString(8, [
     *     ['abcdefghijklmnopqrstuvwxyz', 6], // min 6 characters
     *     ['123456', 1], // min 1 character
     *     ['^$?>!'], // min 1 character
     * ]);
     * ```
     *
     * @param  integer [$length]
     * @param  array   [$alphabets]
     * @return string
     * @throws InvalidParamException if min alphabet length is bigger than the str length
     */
    public function generateRandomString($length = 32, array $alphabets = [])
    {
        if (empty($alphabets)) {
            return parent::generateRandomString($length);
        }

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
            throw new InvalidParamException('The sum of alphabets min length should not be larger than the desired string length!');
        } elseif ($lengthDiff > 0) {
            for ($i = 0; $i < $lengthDiff; $i++) {
                $resultString .= $concatenatedAlphabet[rand(0, strlen($concatenatedAlphabet)-1)];
            }
        }

        // shuffle and return the string
        return str_shuffle($resultString);
    }

    /**
     * Generates random string from english letters (both lower and uppercase).
     *
     * @see `self::generateRandomString()`
     * @param  integer [$length]
     * @return string
     */
    public function generateRandomAlphaString(int $length = 6): string
    {
        return $this->generateRandomString($length, [
            ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length]
        ]);
    }

    /**
     * Checks whether a timestamp token is valid (aka. is not expired).
     *
     * @param  string  $token    Token with timestamp to validate.
     * @param  integer [$expire] Valid token duration time in seconds.
     * @return boolean
     */
    public function isTimestampTokenValid(string $token, int $expire = 3600): bool
    {
        $timestamp = (int) substr($token, strrpos($token, '_') + 1);

        if ($timestamp > 0) {
            return ($timestamp + $expire) >= time();
        }

        return false;
    }
}
