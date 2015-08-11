<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 20:56
 */

namespace app\components;


use app\models\Process;
use app\models\Record;
use yii\db\ActiveQuery;
use yii\db\Expression;

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

    public static function timeline($fromTime, $toTime = null)
    {
        $query = Record::find();
        self::whereFromTo($query, $fromTime, $toTime);
        $query->with(['window', 'window.process']);
//        $query->andWhere(['>=','duration', 30*1000]);
        $records = array_map(function (Record $record) {
            return [
                'id'      => $record->id,
                'window'  => [
                    'id'    => (int)$record->window->id,
                    'title' => $record->window->title,
                ],
                'process' => [
                    'id'   => (int)$record->window->process->id,
                    'name' => $record->window->process->getScreenName()
                ],
                'duration' => $record->duration / 1000,
                'start' => $record->start,
                'end' => $record->end,
                'formattedDuration' => $record->getFormattedDuration(),
                'color' => $record->duration > 3000 ? self::rgbcode($record->window->process->id) : '#000',
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

        $groups = ['children' => [], 'name' => 'root'];
        foreach ($data as $window){
            if (!isset($groups['children'][$window['process_id']])){
                $groups['children'][$window['process_id']] = [
                    'name'     => Process::findOne($window['process_id'])->getScreenName(),
                    'process_id'     => $window['process_id'],
                    'children' => []
                ];
            }
            $groups['children'][$window['process_id']]['children'][] = [
                'name' => $window['title'],
                'size' => (int)$window['duration'] / 1000,
            ];
        }
        $groups['children'] = array_values($groups['children']);
        foreach ($groups['children'] as $key => $process){
            usort($groups['children'][$key]['children'], function($a, $b){
                return $b['size'] - $a['size'];
            });
        }
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

    protected static function whereFromTo(ActiveQuery $query, $fromTime, $toTime = null)
    {
        $timezone = new \DateTimeZone(\Yii::$app->timeZone);
        if ($fromTime) {
            $from = (new \DateTime('now', $timezone))->setTimestamp($fromTime)->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start >= :today',
                [':today' => $from->format('c')]
            );
        }
        if ($toTime) {
            $to = (new \DateTime('now', $timezone))->setTimestamp($toTime)->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start < :todayNight',
                [':todayNight' => $to->format('c')]
            );
        }
        return $query;
    }

    public static function rgbcode($string){
        return '#'.substr(md5($string), 0, 6);
    }
}