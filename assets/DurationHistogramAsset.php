<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 22:14
 */

namespace app\assets;


use yii\web\AssetBundle;

class DurationHistogramAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/durationHistogram.css',
    ];
    public $js = [
        'js/durationHistogram.js'
    ];
    public $depends = [
        '\app\assets\AppAsset',
        '\app\assets\D3Asset',
    ];
}