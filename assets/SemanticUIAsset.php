<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 10.09.15
 * Time: 21:29
 */

namespace app\assets;


use yii\web\AssetBundle;

class SemanticUIAsset extends AssetBundle
{
    public $js = [
        'js/app/semantic/dist/semantic.js'
    ];

    public $css = [
        'js/app/semantic/dist/semantic.css'
    ];

    public $depends = [
        '\app\assets\AppAsset'
    ];
}