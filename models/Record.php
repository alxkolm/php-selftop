<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "metrics".
 *
 * @property integer $id
 * @property integer $window_id
 * @property integer $time
 * @property integer $motions
 * @property integer $clicks
 * @property integer $keys
 * @property string $created
 */
class Record extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%metrics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['window_id'], 'required'],
            [['window_id', 'time', 'motions', 'clicks', 'keys'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'window_id' => Yii::t('app', 'Window ID'),
            'time'      => Yii::t('app', 'Time'),
            'motions'   => Yii::t('app', 'Motions'),
            'clicks'    => Yii::t('app', 'Clicks'),
            'keys'      => Yii::t('app', 'Keys'),
            'created'   => Yii::t('app', 'Created'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWindow(){
        return $this->hasOne(Window::className(), ['id' => 'window_id'])->inverseOf('records');
    }

    public function getFormattedTime()
    {
        $output = round($this->time / 1000 / 60, 2);

        return $output;
    }
}
