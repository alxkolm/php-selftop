<?php

use app\components\widgets\RecordGridView;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RecordSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Records');

?>
<div class="record-index">
    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'layout' => 'inline',
        'fieldConfig' => [
            'labelOptions' => ['class' => ''] // to reset default class enforced by yii
        ],
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

    <?= RecordGridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
    ]); ?>

</div>
