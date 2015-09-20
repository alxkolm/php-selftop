<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 17.09.15
 * Time: 21:12
 */

namespace app\models;


use yii\base\Model;

class DateFilterForm extends Model
{
    public $from;
    public $to;

    public function rules()
    {
        return [
            [['from'], 'date', 'format' => 'php:Y-m-d', 'timestampAttribute' => 'from'],
            [['to'],   'date', 'format' => 'php:Y-m-d', 'timestampAttribute' => 'to'],
        ];
    }
}