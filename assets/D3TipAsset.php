<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 23.12.15
 * Time: 21:55
 */

namespace app\assets;


use yii\web\AssetBundle;

class D3TipAsset extends AssetBundle
{
    public $sourcePath = '@bower/d3-tip';
    public $js = [
        'index.js'
    ];
    public $depends = [
        '\app\assets\D3Asset'
    ];
}