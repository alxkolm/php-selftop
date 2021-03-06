<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 20:56
 */

namespace app\components;


use app\components\TransitionMatrix\Matrix;
use app\models\Key;
use app\models\Process;
use app\models\Record;
use app\models\Task;
use yii\base\Exception;
use yii\base\ExitException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class StatsHelper
{
    const FLATTEN_MATRIX_BY_INDEX = 1;
    const FLATTEN_MATRIX_BY_ID = 2;

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
                new Expression('strftime("%Y-%m-%d %H:%M:00", `at`) as `date`'),
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
                    'date'  => $min->setTimezone($timezone)->format('c'),
                    'count' => (int)$data[$date],
                ];
            } else {
                $out[] = [
                    'date'  => $min->setTimezone($timezone)->format('c'),
                    'count' => 0,
                ];
            }
        }

        return $out;
    }

    /**
     * Return Transition Matrix
     * Number of switch between windows
     * @param $fromTime
     * @param $toTime
     * @param int $durationThreshold
     * @return Matrix
     */
    public static function transitionMatrix($fromTime, $toTime, $durationThreshold = 15000)
    {
        $query = Record::find()
            ->joinWith(['window'])
            ->select([
                'window_id',
            ])
            ->where(['>=','duration', $durationThreshold])
            ->andWhere('window.title != "" AND window.class != ""')
            ->orderBy('start ASC');
        self::whereFromTo($query, $fromTime, $toTime);
        $matrix = new Matrix(['query' => $query]);
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
            return ['id' => (int)$a['id'], 'title' => $a['title']];
        }, $windows);
    }

    /**
     * Convert transition matrix to flat list for D3.js
     * @param $matrix
     * @param $windows
     * @param int $flags
     * @return array
     */
    public  static function flattenTransitionMatrix($matrix, $windows, $flags = self::FLATTEN_MATRIX_BY_INDEX)
    {
        $list = [];
        $winIds = array_map(function ($w) {
            return $w['id'];
        }, $windows);

        foreach ($matrix as $k=>$row){
            foreach ($row as $j => $value){
                switch ($flags){
                    case self::FLATTEN_MATRIX_BY_INDEX:
                        $source = array_search($k, $winIds);
                        $target = array_search($j, $winIds);
                        break;
                    case self::FLATTEN_MATRIX_BY_ID:
                        $source = $k;
                        $target = $j;
                        break;
                    default:
                        throw new Exception('Wrong flags');

                }
                $list[] = [
                    'source' => $source,
                    'target' => $target,
                    'value'  => $value
                ];
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