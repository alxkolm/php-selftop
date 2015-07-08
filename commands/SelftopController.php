<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 08.07.15
 * Time: 21:22
 */

namespace app\commands;


use app\models\Window;
use yii\console\Controller;

class SelftopController extends Controller
{
    /**
     * Output window titles.
     */
    public function actionTitle()
    {
        $titles = Window::find()
            ->select(['title'])
            ->orderBy('title')
            ->createCommand()
            ->queryColumn();
        array_walk(
            array_filter(
                $titles,
                function ($a) { return !empty(trim($a)); }
            ),
            function ($a) { echo $a.PHP_EOL; }
        );
    }
}