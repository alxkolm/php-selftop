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

    public static function whereFromTo(ActiveQuery $query, $fromTime, $toTime = null)
    {
        $timezone = new \DateTimeZone('UTC');
        if ($fromTime) {
            $from = (new \DateTime('now', $timezone))->setTimestamp($fromTime)->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.created >= :today',
                [':today' => $from->format('Y-m-d H:i:s')]
            );
        }
        if ($toTime) {
            $to = (new \DateTime('now', $timezone))->setTimestamp($toTime)->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.created < :todayNight',
                [':todayNight' => $to->format('Y-m-d H:i:s')]
            );
        }
        return $query;
    }
}