<?php
use yii\bootstrap\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\WindowSearch */
?>
<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
])?>
    <?=
        $form->field($searchModel, 'groupBy')
            ->dropDownList([
                'title' => Yii::t('app', 'Title'),
                'class' => Yii::t('app', 'WM class'),
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
        $searchModel->groupBy,
        'formattedDuration',
        'motions',
        'motions_filtered',
        'clicks',
        'keys',
    ],
]); ?>