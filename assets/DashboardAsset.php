<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 22:14
 */

namespace app\assets;


use yii\web\AssetBundle;

class DashboardAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/app/app-dashboard-bundle.js',
    ];
    public $depends = [
        '\app\assets\AppAsset',
    ];
}