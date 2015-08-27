<?php

namespace app\controllers;

use app\assets\AppAsset;
use app\assets\ColorStripAsset;
use app\assets\DurationHistogramAsset;
use app\assets\KeysAreaAsset;
use app\assets\KeysAsset;
use app\assets\SunburstAsset;
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

        // eagerly load process info
        $dataProvider->query->with('process');

        $processList = StatsHelper::getProcessList($today, $todayEnd);
        $this->view->registerJs(
            'var dashboardProcess = '.json_encode($processList),
            View::POS_HEAD);

        $timeline = StatsHelper::timeline($today, $todayEnd);
        $this->view->registerJs(
            'var dashboardTimeline = '.json_encode($timeline),
            View::POS_HEAD);

        $this->view->registerAssetBundle(ColorStripAsset::className());

        $durations = StatsHelper::getProcessWindowHierarchy($today, $todayEnd);
        $this->view->registerJs(
            'var dashboardDurations = '.json_encode($durations),
            View::POS_HEAD);

        $this->view->registerAssetBundle(SunburstAsset::className());

        $keysActivity = StatsHelper::keysActivity($today, $todayEnd);
        $this->view->registerJs(
            'var dashboardKeys = '.json_encode($keysActivity),
            View::POS_HEAD);
        $this->view->registerAssetBundle(KeysAsset::className());
        $this->view->registerAssetBundle(KeysAreaAsset::className());

        return $this->render('dashboard', [
            'dataProvider'  => $dataProvider,
            'searchModel'   => $searchModel,
            'totalActivity' => StatsHelper::totalActivity($today, $todayEnd),
        ]);
    }
}
