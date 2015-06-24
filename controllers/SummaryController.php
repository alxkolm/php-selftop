<?php

namespace app\controllers;

use app\models\Window;
use yii\data\ActiveDataProvider;

class SummaryController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $query = Window::find()
            ->joinWith('records', false)
            ->groupBy('{{window}}.id')
            ->select([
                '{{window}}.*',
                'SUM(time) as time',
                'SUM(motions) as motions',
                'SUM(clicks) as clicks',
                'SUM(keys) as keys',
            ])->orderBy('time DESC');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => 'time DESC'
            ],
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

}
