<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Process */

$this->title = Yii::t('app', 'Create Process');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Processes'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="process-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
