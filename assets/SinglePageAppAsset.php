<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 09.09.15
 * Time: 23:08
 */

namespace app\assets;


use yii\web\AssetBundle;

class SinglePageAppAsset extends AssetBundle
{
    public $js = [
        'js/app.bundle.js'
    ];

    public $depends = [
        '\app\assets\SemanticUIAsset'
    ];
}