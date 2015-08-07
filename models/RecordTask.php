<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "record_task".
 *
 * @property integer $id
 * @property integer $record_id
 * @property integer $task_id
 * @property string $created
 * @property integer $is_prediction
 */
class RecordTask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'record_task';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['record_id', 'task_id'], 'required'],
            [['record_id', 'task_id', 'is_prediction'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'record_id' => Yii::t('app', 'Record ID'),
            'task_id' => Yii::t('app', 'Task ID'),
            'created' => Yii::t('app', 'Created'),
        ];
    }

    public function getTask()
    {
        return $this->hasOne(Task::className(), ['id' => 'task_id']);
    }

    public function getRecord()
    {
        return $this->hasOne(Record::className(), ['id' => 'record_id']);
    }
}
