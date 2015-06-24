<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 24.06.15
 * Time: 23:36
 */

namespace app\components;


class Helper
{
    public static function formatTimeDuration($seconds)
    {
        if ($seconds < 1) {
            $output = '< 1s';
        } elseif ($seconds < 60) {
            $output = floor($seconds) . 's';
        } elseif ($seconds < 60 * 60) {
            $min    = floor($seconds / 60);
            $sec    = floor($seconds - ($min * 60));
            $output = "{$min}m{$sec}s";
        } elseif ($seconds < 60*60*24) {
            $hour   = floor($seconds / (60 * 60));
            $min    = floor(($seconds - $hour * 60 * 60) / 60);
            $sec    = floor($seconds - ($min * 60));
            $output = "{$hour}h{$min}m{$sec}s";
        } else {
            $day    = floor($seconds / (60 * 60 * 24));
            $hour   = floor(($seconds - $day * 60 * 60 * 24) / 60);
            $min    = floor(($seconds - $hour * 60 * 60) / 60);
            $sec    = floor($seconds - ($min * 60));
            $output = "{$day}d{$hour}h{$min}m{$sec}s";
        }

        return $output;
    }
}