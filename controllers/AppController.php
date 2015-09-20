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
use app\models\DateFilterForm;
use app\models\Task;
use app\models\Window;
use app\models\WindowSearch;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
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

//        $this->view->registerAssetBundle(ColorStripAsset::className());

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

        $this->clusterChart($searchModel);

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
        $from = strtotime('today', $searchModel->timestampFrom);
        $to = strtotime('tomorrow', $searchModel->timestampTo);

        $data = $this->clusterData($from, $to);

        $this->view->registerJs(
            'var dashboardClusters = '.json_encode($data['clusters']),
            View::POS_HEAD);

        $this->view->registerJs(
            'var dashboardClustersDurations = '.json_encode($data['durations']),
            View::POS_HEAD);
    }

    public function clusterData($fromTimestamp, $toTimestamp)
    {
        $titles = Window::find()
            ->select(['title'])
            ->distinct(true)
            ->orderBy('title')
            ->createCommand()
            ->queryColumn();
        $titles = array_filter($titles, function ($a) {return trim($a) != '';});
        $clusters = ClusterHelper::clusterizeStrings($titles);

        $from = $fromTimestamp;
        $to = $toTimestamp;

        $clustersList = array_map(function($a){
            return [
                'id' => $a,
                'name' => $a,
            ];
        }, array_unique(array_values($clusters)));

        $durations = ClusterHelper::getProcessWindowHierarchy($clusters, $from, $to);

        return [
            'clusters'  => $clustersList,
            'durations' => $durations,
        ];
    }

    public function actionData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filter = new DateFilterForm();

        $reply = [];

        if (Yii::$app->request->isPost
            && $filter->load(Yii::$app->request->bodyParams, '')
            && $filter->validate())
        {
            $from = strtotime('today', $filter->from ? $filter->from : time());
            $to   = strtotime('tomorrow', $filter->to ? $filter->to : time());

            $reply = [
                'processList' => StatsHelper::getProcessList($from, $to),
                'timeLine' => StatsHelper::timeline($from, $to),
                'durationProcess' => StatsHelper::getProcessWindowHierarchy($from, $to),
                'durationTask' => StatsHelper::getTaskWindowHierarchy($from, $to),
                'keys' => StatsHelper::keysActivity($from, $to)
            ];

            $clusterData = $this->clusterData($from, $to);
            $reply['clusterList'] = $clusterData['clusters'];
            $reply['durationCluster'] = $clusterData['durations'];
        } else {
            throw new BadRequestHttpException;
        }

        return $reply;
    }
}