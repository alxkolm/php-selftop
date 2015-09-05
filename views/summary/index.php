<?php
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\WindowSearch */

$this->title = Yii::t('app', 'Summary');
?>
<?php $form = ActiveForm::begin([
    'method' => 'post',
    'layout' => 'inline',
    'fieldConfig' => [
        'labelOptions' => ['class' => ''] // to reset default class enforced by yii
    ],
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