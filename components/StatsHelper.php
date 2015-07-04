<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 20:56
 */

namespace app\components;


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

    public static function processesStats()
    {
        $sql = 'SELECT
                    process.id,
                    process.name,
                    process.alias,
                    SUM(duration) as duration,
                    SUM(motions) as motions,
                    SUM(motions_filtered) as motions_filtered,
                    SUM(clicks) as clicks,
                    SUM(keys) as keys
                FROM activity
                JOIN window ON window.id = activity.window_id
                JOIN process ON process.id = window.process_id
                WHERE activity.duration > 30000
                GROUP BY window.id
                ';
        $data = \Yii::$app->db->createCommand($sql)->queryAll();
        $data = array_map(function($a) {
            foreach ($a as $key => $value) {
                $a[$key] = is_numeric($value) ? (int) $value : $value;
            }
            return $a;
        }, $data);
        return $data;
    }

    /**
     * Apply constrains on datetime
     * @param ActiveQuery $query
     * @param $fromTime
     * @param null $toTime
     * @return ActiveQuery
     */
    public static function whereFromTo(ActiveQuery $query, $fromTime, $toTime = null)
    {
        $timezone = new \DateTimeZone('UTC');
        if ($fromTime) {
            $from = (new \DateTime('now', $timezone))->setTimestamp($fromTime)->setTimezone($timezone);
            $query->andWhere(
                '{{activity}}.created >= :today',
                [':today' => $from->format('Y-m-d H:i:s')]
            );
        }
        if ($toTime) {
            $to = (new \DateTime('now', $timezone))->setTimestamp($toTime)->setTimezone($timezone);
            $query->andWhere(
                '{{activity}}.created < :todayNight',
                [':todayNight' => $to->format('Y-m-d H:i:s')]
            );
        }
        return $query;
    }
}