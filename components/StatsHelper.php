<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 01.07.15
 * Time: 20:56
 */

namespace app\components;


use app\models\Record;
use yii\db\Expression;

class StatsHelper
{
    public static function totalActivity($fromTime, $toTime = null)
    {
        $query = Record::find();
        $query->select(new Expression('SUM(duration) as duration'));
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
        return $query->scalar();
    }
}