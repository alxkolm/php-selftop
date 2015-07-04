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

<div style="margin: 1em;"><strong>Total activity:</strong> <?= Helper::formatTimeDuration($totalActivity / 1000)?></div>
<div id="graph-duration-histrogram"></div>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'process.screenName',
        [
            'attribute' => 'title',
            'visible'   => $searchModel->groupBy == 'title'
        ],
        'formattedDuration',
        'motions',
        'motions_filtered',
        'clicks',
        'keys',
    ],
]); ?>