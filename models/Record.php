<?php

namespace app\models;

use app\components\Helper;
use Yii;

/**
 * This is the model class for table "metrics".
 *
 * @property integer $id
 * @property integer $window_id
 * @property integer $start
 * @property integer $end
 * @property integer $duration
 * @property integer $motions
 * @property integer $motions_filtered
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
        return '{{%record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['window_id'], 'required'],
            [['window_id', 'duration', 'motions', 'motions_filtered', 'clicks', 'keys'], 'integer']
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
            'duration'      => Yii::t('app', 'Duration'),
            'motions'   => Yii::t('app', 'Motions'),
            'motions_filtered'   => Yii::t('app', 'Motions (filtered)'),
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

    public function afterFind()
    {
        $timestamp = new \DateTime($this->created, new \DateTimeZone('UTC'));
        $timestamp->setTimezone(new \DateTimeZone(Yii::$app->timeZone));
        $this->created = $timestamp->format('Y-m-d H:i:s');
        return parent::afterFind();
    }


    public function getFormattedDuration()
    {
        return Helper::formatTimeDuration($this->duration / 1000);
    }
}
