<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 25.07.15
 * Time: 17:10
 */

namespace app\components\widgets;


use Yii;
use yii\grid\GridView;

class RecordGridView extends GridView
{
    public function init()
    {

        $this->columns = [
            'id',
            'pid',
            'window.process.screenName:text:Process',
            'window.title',
            'formattedDuration:text:Duration',
            'motions',
            'clicks',
            'scrolls',
            'keys',
            [
                'attribute' => 'tasks',
                'content' => function ($model, $key, $index, $column) {
                    $tasks = [];
                    foreach ($model->tasks as $task){
                        $tasks[] = "<span class='badge'>{$task->name}</span>";
                    }
                    return implode("\n", $tasks);
                },
            ],
            [
                'attribute' => 'created',
                'content' => function ($model) {
                    $timestamp = new \DateTime($model->created, new \DateTimeZone('UTC'));
                    $timestamp->setTimezone(new \DateTimeZone(Yii::$app->timeZone));
                    return $timestamp->format('Y-m-d H:i:s');
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ];
        parent::init();
    }
}