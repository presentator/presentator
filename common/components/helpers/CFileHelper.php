<?php
namespace common\components\helpers;

use Yii;
use yii\helpers\FileHelper;

/**
 * Custom FileHelper class.
 *
 * @author Gani Georgiev <gani.georgiev@gmail.com>
 */
class CFileHelper extends FileHelper
{
    /**
     * Returns absolute public url from local path string.
     * @param  string  $path     The path to be converted.
     * @param  boolean $absolute Whether to return absolute or relative url.
     * @return string
     */
    public static function getUrlFromPath($path, $absolute = true)
    {
        if ($absolute) {
            // absolute url
            return self::replaceUploadString(
                $path,
                rtrim(Yii::getAlias('@mainWeb'), '/'),
                rtrim(Yii::$app->params['publicUrl'], '/')
            );
        }

        // relative url
        return '/' . ltrim(
            self::replaceUploadString($path, Yii::getAlias('@mainWeb'), ''),
            '/'
        );
    }

    /**
     * Returns local path from an url string.
     * @param  string $url
     * @return string
     */
    public static function getPathFromUrl($url)
    {
        if (strpos($url, Yii::$app->params['publicUrl']) === 0) {
            // path from absolute url
            return self::replaceUploadString(
                $url,
                rtrim(Yii::$app->params['publicUrl'], '/'),
                rtrim(Yii::getAlias('@mainWeb'), '/')
            );
        }

        // path from relative url
        return Yii::getAlias('@mainWeb') . '/' . ltrim($url, '/');
    }


    /**
     * Handles the replacement of local upload string.
     * @param  string $str
     * @param  string $needle
     * @param  string $replace
     * @return string
     */
    private static function replaceUploadString($str, $needle, $replace)
    {
        $pos = strpos($str, $needle);

        if ($pos !== false) {
            return $replace . substr($str, $pos + strlen($needle));
        }

        return $str;
    }
}
