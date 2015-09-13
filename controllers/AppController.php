<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 09.09.15
 * Time: 23:10
 */

namespace app\controllers;


use app\assets\ColorStripAsset;
use app\assets\DashboardAsset;
use app\assets\KeysAreaAsset;
use app\assets\KeysAsset;
use app\assets\SinglePageAppAsset;
use app\assets\SunburstAsset;
use app\components\ClusterHelper;
use app\components\StatsHelper;
use app\models\Task;
use app\models\Window;
use app\models\WindowSearch;
use Yii;
use yii\web\Controller;
use yii\web\View;

class AppController extends Controller
{
    public $layout = 'app';

    public function actionIndex(){
        $this->view->registerAssetBundle(SinglePageAppAsset::className());
        $this->exposeData();

        return $this->render('index');
    }

    public function exposeData(){
        $searchModel           = new WindowSearch();
        $searchModel->dateFrom = date('Y-m-d');
        $searchModel->dateTo   = date('Y-m-d');

        $dataProvider          = $searchModel->search(Yii::$app->request->post());

        // eagerly load process info
        $dataProvider->query->with('process');

        $from = strtotime('today', $searchModel->timestampFrom);
        $to = strtotime('tomorrow', $searchModel->timestampTo);

        $processList = StatsHelper::getProcessList($from, $to);
        $this->view->registerJs(
            'var dashboardProcess = '.json_encode($processList),
            View::POS_HEAD);

        $timeline = StatsHelper::timeline($from, $to);
        $this->view->registerJs(
            'var dashboardTimeline = '.json_encode($timeline),
            View::POS_HEAD);

        $this->view->registerAssetBundle(ColorStripAsset::className());

        // Durations split by process
        $durations = StatsHelper::getProcessWindowHierarchy($from, $to);
        $this->view->registerJs(
            'var dashboardDurations = '.json_encode($durations),
            View::POS_HEAD);

//        $this->view->registerAssetBundle(SunburstAsset::className());

        // Durations split by task
        $durations = StatsHelper::getTaskWindowHierarchy($from, $to);
        $this->view->registerJs(
            'var dashboardTaskDurations = '.json_encode($durations),
            View::POS_HEAD);

        // Keys
        $keysActivity = StatsHelper::keysActivity($from, $to);
        $this->view->registerJs(
            'var dashboardKeys = '.json_encode($keysActivity),
            View::POS_HEAD);
        $this->view->registerAssetBundle(KeysAsset::className());
        $this->view->registerAssetBundle(KeysAreaAsset::className());

//        $this->clusterChart($searchModel);

        $tasks = array_map(function ($task) {
            return [
                'id'   => $task->id,
                'name' => $task->name,
            ];
        }, Task::find()->all());
        $this->view->registerJs(
            'var dashboardTasks = '.json_encode($tasks),
            View::POS_HEAD);

        $this->view->registerAssetBundle(DashboardAsset::className());
    }

    public function clusterChart($searchModel)
    {
        $titles = Window::find()
            ->select(['title'])
            ->distinct(true)
            ->orderBy('title')
            ->createCommand()
            ->queryColumn();
        $titles = array_filter($titles, function ($a) {return trim($a) != '';});
        $clusters = ClusterHelper::clusterizeStrings($titles);

        $from = strtotime('today', $searchModel->timestampFrom);
        $to = strtotime('tomorrow', $searchModel->timestampTo);

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