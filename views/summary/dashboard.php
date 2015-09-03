<?php
use app\components\Helper;
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\WindowSearch */
?>
<?php $form = ActiveForm::begin([
    'method' => 'post',
])?>
    <?=
        $form->field($searchModel, 'groupBy')
            ->dropDownList([
                'title' => Yii::t('app', 'Title'),
                'process_id' => Yii::t('app', 'Process'),
            ])
    ?>

    <?=
        $form->field($searchModel, 'dateFrom')->widget(DatePicker::className(), [
            'dateFormat' => 'yyyy-MM-dd',
        ])
    ?>
    <?=
        $form->field($searchModel, 'dateTo')->widget(DatePicker::className(), [
            'dateFormat' => 'yyyy-MM-dd',
        ])
    ?>

    <?= \yii\bootstrap\Button::widget([
        'label' => 'Submit',
        'options' => ['class' => 'btn-primary']
    ]) ?>
<?php ActiveForm::end(); ?>
<div id="keys-activity-area" class="keys-activity-area clearfix"></div>
<div id="keys-activity" class="keys-activity clearfix"></div>
<div id="color-strip" class="color-strip clearfix"></div>
<div id="color-strip-clusters" class="color-strip clearfix"></div>
<div id="sunburst" class="sunburst">
    <div class="info">
        <div class="percentage"></div>
        <div class="duration"></div>
        <div class="window"></div>
    </div>

</div>
<div id="sunburst-process" class="sunburst-process clearfix"></div>

<div style="margin: 1em;" class="clearfix"><strong>Total activity:</strong> <?= Helper::formatTimeDuration($totalActivity / 1000)?></div>
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