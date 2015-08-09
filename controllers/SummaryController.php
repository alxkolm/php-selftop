<?php

namespace app\controllers;

use app\assets\AppAsset;
use app\assets\ColorStripAsset;
use app\assets\DurationHistogramAsset;
use app\components\StatsHelper;
use app\models\Record;
use app\models\Window;
use app\models\WindowSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\web\View;

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
        $today                 = strtotime('today');
        $todayEnd              = strtotime('today 23:59:59');
        $searchModel           = new WindowSearch();
        $searchModel->dateFrom = $today;
        $searchModel->dateTo   = $todayEnd;
        $dataProvider          = $searchModel->search(Yii::$app->request->queryParams);

        $this->view->registerJs(
            'var dashboardDurations = '.json_encode(StatsHelper::durations($today, $todayEnd)),
            View::POS_END);

        $timeline = StatsHelper::timeline($today, $todayEnd);
        $this->view->registerJs(
            'var dashboardTimeline = '.json_encode($timeline),
            View::POS_END);

        $this->view->registerAssetBundle(DurationHistogramAsset::className());
        $this->view->registerAssetBundle(ColorStripAsset::className());

        return $this->render('dashboard', [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'totalActivity' => StatsHelper::totalActivity($today, $todayEnd),
        ]);
    }
}
