<?php
use app\components\Helper;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\WindowSearch */
?>
<?php $form = ActiveForm::begin([
    'method' => 'get',
])?>
    <?=
        $form->field($searchModel, 'groupBy')
            ->dropDownList([
                'title' => Yii::t('app', 'Title'),
                'process_id' => Yii::t('app', 'Process'),
            ])
    ?>

    <?= \yii\bootstrap\Button::widget([
        'label' => 'Submit',
        'options' => ['class' => 'btn-primary']
    ]) ?>
<?php ActiveForm::end(); ?>
<div id="color-strip" class="color-strip clearfix"></div>
<div id="sunburst" class="sunburst">
    <div class="info">
        <div class="percentage"></div>
        <div class="window"></div>
    </div>

</div>
<div style="margin: 1em;"><strong>Total activity:</strong> <?= Helper::formatTimeDuration($totalActivity / 1000)?></div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'process.screenName',
        [
            'attribute' => 'title',
            'visible'   => $searchModel->groupBy == 'title',
            'value' => function ($model, $key, $index, $column) {
                return \yii\helpers\Html::a($model->title, ['window/view', 'id' => $model->id]);
            },
            'format' => 'html',
        ],
        'formattedDuration',
        'motions',
        'motions_filtered',
        'clicks',
        'scrolls',
        'keys',
    ],
]); ?>