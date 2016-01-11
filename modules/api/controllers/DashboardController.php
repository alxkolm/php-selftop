<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 10.01.16
 * Time: 12:09
 */

namespace app\modules\api\controllers;


use app\components\ClusterHelper;
use app\components\StatsHelper;
use app\models\DateFilterForm;
use Yii;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'corsFilter' => [
                'class' => \yii\filters\Cors::className(),
            ],
        ];
    }

    public function actionIndex($fields = [])
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $filter = new DateFilterForm();

        $reply = [];

        if ($filter->load(Yii::$app->request->get(), '')
            && $filter->validate())
        {
            $from = strtotime('today', $filter->date ? $filter->date : time());
            $to   = strtotime('tomorrow', $filter->date ? $filter->date : time());

            $reply = [
                'processes' => StatsHelper::getProcessList($from, $to),
            ];

            if (empty($fields) || in_array('timeLine', $fields)){
                $reply['timeLine'] = StatsHelper::timeline($from, $to);
            }

            if (empty($fields) || in_array('processDuration', $fields)){
                $reply['processDuration'] = StatsHelper::getProcessWindowHierarchy($from, $to);
            }

            if (empty($fields) || in_array('keys', $fields)){
                $reply['keys'] = StatsHelper::keysActivity($from, $to);
            }

            if (empty($fields) || in_array('clusterDuration', $fields)){
                $clusterData = $this->clusterData($from, $to);
                $reply['clusters'] = $clusterData['clusters'];
                $reply['clusterDuration'] = $clusterData['durations'];
            }
        } else {
            throw new BadRequestHttpException;
        }

        return $reply;
    }

    public function clusterData($fromTimestamp, $toTimestamp)
    {

        $windows = StatsHelper::windows($fromTimestamp, $toTimestamp);
        list($clusters, $winIdCluster) = ClusterHelper::clusterizeStrings($windows);

        $from = $fromTimestamp;
        $to = $toTimestamp;

        $clustersList = array_map(function($a){
            return [
                'id' => $a,
                'name' => $a,
            ];
        }, array_unique(array_values($clusters)));

        $durations = ClusterHelper::getProcessWindowHierarchy($winIdCluster, $from, $to);

        return [
            'clusters'  => $clustersList,
            'durations' => $durations,
        ];
    }
}