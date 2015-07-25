<?php

use app\components\widgets\RecordGridView;
use app\models\Task;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Window */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Windows'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="window-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'process_id',
            'title:ntext',
            'class:ntext',
            'created',
        ],
    ]) ?>

    <?= Html::beginForm(['window/clear-task', 'id' => $model->id]) ?>
    <?= Html::submitButton('Clear all tasks') ?>
    <?= Html::endForm() ?>

    <?= Html::beginForm(['window/assign-task', 'id' => $model->id]) ?>
    <?= Html::dropDownList('task_id', null, ArrayHelper::map(Task::find()->all(), 'id', 'name')) ?>
    <?= Html::submitButton('Assign task') ?>
    <?= Html::endForm() ?>




    <?= RecordGridView::widget([
        'dataProvider' => $recordsDataProvider,
    ]); ?>

</div>
