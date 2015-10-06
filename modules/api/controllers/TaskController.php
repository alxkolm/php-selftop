<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 06.10.15
 * Time: 20:58
 */

namespace app\modules\api\controllers;

use yii\rest\ActiveController;

class TaskController extends ActiveController
{
    public $modelClass = 'app\models\Task';
}