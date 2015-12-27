<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 20:56
 */

namespace app\components;


use app\models\Key;
use app\models\Process;
use app\models\Record;
use app\models\Task;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class StatsHelper
{
    public static function totalActivity($fromTime, $toTime = null)
    {
        $query = Record::find();
        $query->select(new Expression('SUM(duration) as duration'));
        self::whereFromTo($query, $fromTime, $toTime);
        return $query->scalar();
    }

    public static function durations($fromTime, $toTime = null)
    {
        $query = Record::find();
        self::whereFromTo($query, $fromTime, $toTime);
        $query->select(new Expression('duration'));
        $values = $query->column();
        $values = array_map(function ($a){return floor($a / 1000);}, $values);
        $values = array_filter($values, function ($a) {return $a >= 5;});
        return array_values($values);
    }

    /**
     * Map process with same name under one id
     * @return array
     */
    public static function processMap()
    {
        /** @var Process[] $processes */
        $processes = Process::find()->all();
        $out = [];
        $names = [];
        $ignore = [
            'java'
        ];
        foreach ($processes as $process) {
            if (!isset($names[$process->name]) || in_array($process->name, $ignore)) {
                $names[$process->name] = $process;
            }
            $out[$process->id] = $names[$process->name];
        }

        return $out;
    }

    public static function timeline($fromTime, $toTime = null)
    {
        $query = Record::find();
        self::whereFromTo($query, $fromTime, $toTime);
        $query->with(['window', 'window.process']);
        $processMap = self::processMap();
        $records = array_map(function (Record $record) use ($processMap) {
            $mappedProcess = $processMap[$record->window->process->id];
            return [
                'id'      => $record->id,
                'window'  => [
                    'id'    => (int)$record->window->id,
                    'title' => $record->window->title,
                ],
                'process' => [
                    'id'   => (int) $mappedProcess->id,
                    'name' => $mappedProcess->getScreenName()
                ],
                'duration' => $record->duration / 1000,
                'start' => $record->start,
                'end' => $record->end,
                'formattedDuration' => $record->getFormattedDuration(),
                'color' => $record->duration > 3000 ? self::rgbcode($mappedProcess->id) : '#000',
            ];
        }, $query->all());
        $totalDuration = array_reduce($records, function ($a, $b) {return $a + $b['duration'];}, 0);
        array_walk($records, function (&$v) use ($totalDuration) {
            $v['percent'] = ($v['duration'] / $totalDuration)*100;
        });

        return $records;
    }

    public static function getProcessWindowHierarchy($fromTime, $toTime = null)
    {
        $query = Record::find();
        self::whereFromTo($query, $fromTime, $toTime);
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
        $processMap = self::processMap();

        $groups = ['children' => [], 'name' => 'root'];
        // build tree of processes
        foreach ($data as $window){
            if ((int) $window['duration'] == 0) {
                continue;
            }
            $process = $processMap[$window['process_id']];
            if (!isset($groups['children'][$process->id])){
                $groups['children'][$process->id] = [
                    'name'       => $process->getScreenName(),
                    'sector_id'  => $process->id,
                    'process_id' => $process->id,
                    'children'   => [],
                    'size'       => 0,
                    'color'      => self::rgbcode($process->id),
                ];
            }
            $groups['children'][$process->id]['children'][] = [
                'name'      => $window['title'],
                'window_id' => $window['window_id'],
                'size'      => (int) $window['duration'] / 1000,
            ];
            $groups['children'][$process->id]['size'] += (int) $window['duration'] / 1000;
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

    public static function getTaskWindowHierarchy($fromTime, $toTime = null)
    {
        $query = Record::find();
        self::whereFromTo($query, $fromTime, $toTime);
        $query->joinWith(['tasks', 'window']);
        $query->groupBy('window_id');
        $query->select([
            'SUM(duration) as duration',
            'task_id',
            'window_id',
            'window.title'
        ]);
        $data = $query->createCommand()->queryAll();

//        /** @var Process[] $processMap */
//        $processMap = self::processMap();

        $tasks = ArrayHelper::map(Task::find()->all(), 'id', 'name');

        $groups = ['children' => [], 'name' => 'root'];
        // build tree of processes
        foreach ($data as $window){
            if ((int) $window['duration'] == 0) {
                continue;
            }
            $taskId = $window['task_id'] ? $window['task_id'] : -1;
            $taskName = $taskId != -1 ? $tasks[$taskId] : 'n/a';
            if (!isset($groups['children'][$taskId])){
                $groups['children'][$taskId] = [
                    'name'       => $taskName,
                    'sector_id'  => $taskId,
                    'process_id' => $taskId,
                    'children'   => [],
                    'size'       => 0,
                    'color'      => self::rgbcode($taskId),
                ];
            }
            $groups['children'][$taskId]['children'][] = [
                'name'      => $window['title'],
                'window_id' => $window['window_id'],
                'size'      => (int) $window['duration'] / 1000,
            ];
            $groups['children'][$taskId]['size'] += (int) $window['duration'] / 1000;
        }
        $groups['children'] = array_values($groups['children']);
        foreach ($groups['children'] as $key => $task){
            usort($groups['children'][$key]['children'], function($a, $b){
                return $b['size'] - $a['size'];
            });
        }
        usort($groups['children'], function($a, $b){
            return $b['size'] - $a['size'];
        });
        return $groups;
    }

    public static function getProcessList($fromTime, $toTime = null)
    {
        $query = Record::find();
        $query->joinWith('window.process');
        $query->groupBy('process_id');
        self::whereFromTo($query, $fromTime, $toTime);
        $r = $query->all();

        return array_map(function ($a) {return ['id' =>$a->window->process->id, 'name' => $a->window->process->screenName];}, $r);
    }

    public static function keysActivity($fromTime, $toTime = null)
    {
        $query = Key::find()
            ->select([
                new Expression('COUNT(*) as count'),
                new Expression('strftime("%Y-%m-%d %H:%M:00", `at`, "localtime") as `date`'),
            ])
            ->groupBy(new Expression('strftime("%Y-%m-%d %H:%M", `at`) '));

        self::whereFromTo($query, $fromTime, $toTime, 'at');
        $data = $query->createCommand()->queryAll();
        array_walk($data, function (&$a) {
            $a['count'] = (int)$a['count'];
        });

        $data = ArrayHelper::map($data, 'date', 'count');

        $timezone = new \DateTimeZone(\Yii::$app->timeZone);
        $from = (new \DateTime('@'.$fromTime))->setTimezone($timezone);
        $to = (new \DateTime('@'.$toTime))->setTimezone($timezone);

        $interval = new \DateInterval('PT1M');
        $period   = new \DatePeriod($from, $interval, $to);


        $out = [];
        foreach ($period as $min){
            $date = $min->format('Y-m-d H:i:00');
            if (isset($data[$date])){
                $out[] = [
                    'date'  => $min->setTimezone($timezone)->format('Y-m-d H:i:s'),
                    'count' => (int)$data[$date],
                ];
            } else {
                $out[] = [
                    'date'  => $min->setTimezone($timezone)->format('Y-m-d H:i:s'),
                    'count' => 0,
                ];
            }
        }

        return $out;
    }

    /**
     * Return Transition Matrix
     * Number of switch between windows
     */
    public static function transitionMatrix($fromTime, $toTime)
    {
        $query = Record::find()
            ->joinWith(['window'])
            ->select([
                'window_id',
            ])
            ->where('duration >= 10000')
            ->andWhere('window.title != "" AND window.class != ""')
            ->orderBy('start ASC');
        self::whereFromTo($query, $fromTime, $toTime);
        $data = $query->createCommand()->queryColumn();
        $matrix = [];
        $prevWindow = array_shift($data);
        foreach ($data as $windowId){
            if (!isset($matrix[$prevWindow])){
                $matrix[$prevWindow] = [];
            }

            if (!isset($matrix[$prevWindow][$windowId])){
                $matrix[$prevWindow][$windowId] = 0;
            }

            $matrix[$prevWindow][$windowId]++;

            $prevWindow = $windowId;
        }
        return $matrix;
    }

    public static function windows($fromTime, $toTime)
    {
        $query = Record::find()
            ->joinWith(['window'])
            ->distinct()
            ->select([
                'window_id as id',
                'window.title'
            ]);
        self::whereFromTo($query, $fromTime, $toTime);
        return $query->createCommand()->queryAll();
    }

    public static function windowsList($windows)
    {
        return array_map(function ($a) {
            return ['id' => $a['id'], 'title' => $a['title']];
        }, $windows);
    }

    /**
     * Convert transition matrix to flat list for D3.js
     */
    public  static function flattenTransitionMatrix($matrix, $windows)
    {
        $list = [];
        $winIds = array_map(function ($w) {
            return $w['id'];
        }, $windows);

        foreach ($matrix as $k=>$row){
            foreach ($row as $j => $value){
                $list[] = ['source' => array_search($k, $winIds), 'target' => array_search($j, $winIds), 'value' => $value];
            }
        }
        return $list;
    }

    public static function whereFromTo(ActiveQuery $query, $fromTime, $toTime = null, $column = '{{record}}.start')
    {
        $timezone = new \DateTimeZone(\Yii::$app->timeZone);
        if ($fromTime) {
            $from = (new \DateTime('@'.$fromTime))->setTimezone($timezone);
            $query->andWhere(
                $column . ' >= :today',
                [':today' => $from->format('c')]
            );
        }
        if ($toTime) {
            $to = (new \DateTime('@'.$toTime))->setTimezone($timezone);
            $query->andWhere(
                $column . ' < :todayNight',
                [':todayNight' => $to->format('c')]
            );
        }
        return $query;
    }

    public static function rgbcode($string){
        return '#'.substr(md5($string), 0, 6);
    }
}