<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/jquery-ui.min.css',
        'css/color-picker.min.css',
        'css/style.css',
    ];

    public $js = [
        'js/modernizr.min.js',
        'js/jquery-ui.min.js',
        'js/dropzone.js',
        'js/color-picker.min.js',
        'js/pr.js',
        'js/cookies.js',
        'js/popup.js',
        'js/context-menu.js',
        'js/selectify.js',
        'js/tabs.js',
        'js/mention.js',
        'js/slider.js',
        'js/app.js',
    ];
    public $depends = [
        'yii\web\YiiAsset'
    ];
}
