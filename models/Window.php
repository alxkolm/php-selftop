<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "window".
 *
 * @property integer $id
 * @property string $title
 * @property string $class
 * @property integer $created
 */
class Window extends \yii\db\ActiveRecord
{
    public $time;
    public $motions;
    public $clicks;
    public $keys;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%window}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'class'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'      => Yii::t('app', 'ID'),
            'title'   => Yii::t('app', 'Title'),
            'class'   => Yii::t('app', 'Class'),
            'created' => Yii::t('app', 'Created'),
        ];
    }

    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['window_id' => 'id'])->inverseOf('window');
    }

    public function getFormattedTime()
    {
        $output = round($this->time / 1000 / 60, 2);

        return $output;
    }
}
