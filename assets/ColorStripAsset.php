<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 22:14
 */

namespace app\assets;


use yii\web\AssetBundle;

class ColorStripAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/colorStrip.css',
    ];
    public $js = [
        'js/dashboard.js',
        'js/colorStrip.js',
    ];
    public $depends = [
        '\app\assets\AppAsset',
        '\app\assets\D3Asset',
    ];
}