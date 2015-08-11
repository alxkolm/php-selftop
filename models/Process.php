<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "process".
 *
 * @property integer $id
 * @property string $name
 * @property string alias
 * @property string $created
 */
class Process extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{process}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['alias'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'alias' => Yii::t('app', 'Alias'),
            'created' => Yii::t('app', 'Created'),
        ];
    }

    public function getWindows()
    {
        return $this->hasMany(Window::className(), ['process_id' => 'id']);
    }

    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['window_id' => 'id'])->via('windows');
    }

    public function getScreenName()
    {
        return $this->alias ? $this->alias : ($this->name ? $this->name : 'n/a');
    }
}
