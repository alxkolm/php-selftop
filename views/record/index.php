<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Records');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="record-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Record'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
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
        ],
    ]); ?>

</div>
