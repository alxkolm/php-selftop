<?php

namespace app\controllers;

use app\models\Window;
use app\models\WindowSearch;
use Yii;
use yii\data\ActiveDataProvider;

class SummaryController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $searchModel  = new WindowSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel           = new WindowSearch();
        $searchModel->dateFrom = strtotime('today');
        $searchModel->dateTo   = strtotime('today 23:59:59');
        $dataProvider          = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('dashboard', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }
}
