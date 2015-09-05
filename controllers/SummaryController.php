<?php

namespace app\controllers;

use app\assets\AppAsset;
use app\assets\ColorStripAsset;
use app\assets\ColorStripClustersAsset;
use app\assets\DurationHistogramAsset;
use app\assets\KeysAreaAsset;
use app\assets\KeysAsset;
use app\assets\SunburstAsset;
use app\components\ClusterHelper;
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
        $searchModel->dateFrom = date('Y-m-d');
        $searchModel->dateTo   = date('Y-m-d');
        $dataProvider = $searchModel->search(Yii::$app->request->post());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel'  => $searchModel,
        ]);
    }

    public function actionDashboard()
    {
        $searchModel           = new WindowSearch();
        $searchModel->dateFrom = date('Y-m-d');
        $searchModel->dateTo   = date('Y-m-d');

        $dataProvider          = $searchModel->search(Yii::$app->request->queryParams);

        // eagerly load process info
        $dataProvider->query->with('process');

        $from = strtotime('today', $searchModel->timestampFrom);
        $to = strtotime('today 23:59:59', $searchModel->timestampTo);

        $processList = StatsHelper::getProcessList($from, $to);
        $this->view->registerJs(
            'var dashboardProcess = '.json_encode($processList),
            View::POS_HEAD);

        $timeline = StatsHelper::timeline($from, $to);
        $this->view->registerJs(
            'var dashboardTimeline = '.json_encode($timeline),
            View::POS_HEAD);

        $this->view->registerAssetBundle(ColorStripAsset::className());

        $durations = StatsHelper::getProcessWindowHierarchy($from, $to);
        $this->view->registerJs(
            'var dashboardDurations = '.json_encode($durations),
            View::POS_HEAD);

        $this->view->registerAssetBundle(SunburstAsset::className());

        $keysActivity = StatsHelper::keysActivity($from, $to);
        $this->view->registerJs(
            'var dashboardKeys = '.json_encode($keysActivity),
            View::POS_HEAD);
        $this->view->registerAssetBundle(KeysAsset::className());
        $this->view->registerAssetBundle(KeysAreaAsset::className());

        $this->clusterChart();

        return $this->render('dashboard', [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'totalActivity' => StatsHelper::totalActivity($from, $to),
        ]);
    }

    public function clusterChart()
    {
        $titles = Window::find()
            ->select(['title'])
            ->distinct(true)
            ->orderBy('title')
            ->createCommand()
            ->queryColumn();
        $titles = array_filter($titles, function ($a) {return trim($a) != '';});
        $clusters = ClusterHelper::clusterizeStrings($titles);

        $from = strtotime('today');
        $to = strtotime('today 23:59:59');

        $clustersList = array_map(function($a){
            return [
                'id' => $a,
                'name' => $a,
            ];
        }, array_unique(array_values($clusters)));
        $this->view->registerJs(
            'var dashboardClusters = '.json_encode($clustersList),
            View::POS_HEAD);

        $durations = ClusterHelper::getProcessWindowHierarchy($clusters, $from, $to);
        $this->view->registerJs(
            'var dashboardClustersDurations = '.json_encode($durations),
            View::POS_HEAD);

        $this->view->registerAssetBundle(SunburstAsset::className());
    }
}
