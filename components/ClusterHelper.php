<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 03.09.15
 * Time: 21:29
 */

namespace app\components;


use app\models\Record;
use yii\base\Exception;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;

class ClusterHelper
{
    public static function clusterizeStrings(array $windows)
    {
        $path = \Yii::getAlias('@runtime/string_cluster');
        FileHelper::createDirectory($path);
        /** @var string $filename Temp file */
        $filename = tempnam($path, 'tmp');


        // Write strings to file
        $f = fopen($filename, 'w');
        foreach ($windows as $win){
            fwrite($f, $win['title'].PHP_EOL);
        }
        fclose($f);
        chmod($filename, 0666);

        // make clusters
        $cmdPath = \Yii::getAlias('@app/scikit/string-cluster.py');
        $cmd = "python {$cmdPath} < {$filename}";
        $clusterRaw = [];
        $exitCode = 0;
        $result = exec($cmd, $clusterRaw, $exitCode);
        if ($exitCode != 0){
            return [];
//            throw new Exception('Can\'t run cluster command. ' . $result);
        }
        unlink($filename);

        // Transform results
        $winIdCluster = [];
        foreach ($windows as $key => $window){
            $winIdCluster[$window['id']] = $clusterRaw[$key];
        }

        return [$clusterRaw, $winIdCluster];
    }

    public static function timeline($clusters, $fromTime, $toTime = null)
    {
        $query = Record::find();
        StatsHelper::whereFromTo($query, $fromTime, $toTime);
        $query->with(['window']);
        $records = array_map(function (Record $record) use ($clusters) {
            $clusterId = isset($clusters[trim($record->window->title)]) ? $clusters[trim($record->window->title)] : '-1';
            return [
                'id'      => $record->id,
                'window'  => [
                    'id'    => (int)$record->window->id,
                    'title' => $record->window->title,
                ],
                'process' => [
                    'id'   => (int) $clusterId,
                    'name' => 'Cluster #'.$clusterId
                ],
                'duration' => $record->duration / 1000,
                'start' => $record->start,
                'end' => $record->end,
                'formattedDuration' => $record->getFormattedDuration(),
            ];
        }, $query->all());
        $totalDuration = array_reduce($records, function ($a, $b) {return $a + $b['duration'];}, 0);
        array_walk($records, function (&$v) use ($totalDuration) {
            $v['percent'] = ($v['duration'] / $totalDuration)*100;
        });

        return $records;
    }

    public static function getProcessWindowHierarchy($winIdCluster, $fromTime, $toTime = null)
    {
        $query = Record::find();
        StatsHelper::whereFromTo($query, $fromTime, $toTime);
        $query->joinWith(['window', 'window.process']);
        $query->groupBy('window_id');
        $query->select([
            'SUM(duration) as duration',
            'process_id',
            'window_id',
            'window.title'
        ]);
        $data = $query->createCommand()->queryAll();

        /** @var Process[] $processMap */
//        $processMap = self::processMap();

        $groups = ['children' => [], 'name' => 'root'];
        // build tree of processes
        foreach ($data as $window){
            if ((int) $window['duration'] == 0) {
                continue;
            }
            $clusterId = isset($winIdCluster[$window['window_id']]) ? $winIdCluster[$window['window_id']] : '-1';
            if (!isset($groups['children'][$clusterId])){
                $groups['children'][$clusterId] = [
                    'name'       => 'Cluster #'.$clusterId,
                    'sector_id'  => $clusterId,
                    'process_id' => $window['process_id'],
                    'children'   => [],
                    'size'       => 0,
                ];
            }
            $groups['children'][$clusterId]['children'][] = [
                'name'      => $window['title'],
                'window_id' => $window['window_id'],
                'size'      => (int) $window['duration'] / 1000,
            ];
            $groups['children'][$clusterId]['size'] += (int) $window['duration'] / 1000;
        }
        $groups['children'] = array_values($groups['children']);
        foreach ($groups['children'] as $key => $process){
            usort($groups['children'][$key]['children'], function($a, $b){
                return $b['size'] - $a['size'];
            });
        }
        usort($groups['children'], function($a, $b){
            return $b['size'] - $a['size'];
        });
        return $groups;
    }
}