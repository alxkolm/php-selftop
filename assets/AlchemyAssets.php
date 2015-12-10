<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 10.12.15
 * Time: 21:27
 */

namespace app\assets;


use yii\web\AssetBundle;

class AlchemyAssets extends AssetBundle
{
    public $css = [
        '/vendor/bower/alchemyjs/dist/styles/vendor.css',
        '/vendor/bower/alchemyjs/dist/alchemy.css'
    ];
    public $js = [
        '/vendor/bower/alchemyjs/dist/scripts/vendor.js',
//        '/vendor/bower/lodash/dist/lodash.min.js',
        '/vendor/bower/alchemyjs/dist/alchemy.js'
    ];
    public $depends = [
        '\app\assets\D3Asset',
    ];
}