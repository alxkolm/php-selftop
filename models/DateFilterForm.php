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
    public $date;

    public function rules()
    {
        return [
            [['date'], 'date', 'format' => 'php:Y-m-d', 'timestampAttribute' => 'date'],
        ];
    }
}