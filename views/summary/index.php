<?php
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        'id',
        'title',
        'formattedDuration',
        'motions',
        'motions_filtered',
        'clicks',
        'keys',
    ],
]); ?>