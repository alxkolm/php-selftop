<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 22:24
 */

namespace app\assets;


use yii\web\AssetBundle;

class D3Asset extends AssetBundle
{
    public $sourcePath = null;
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/d3/3.5.5/d3.min.js'
    ];
}