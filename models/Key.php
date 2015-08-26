<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "keys".
 *
 * @property integer $id
 * @property integer $window_id
 * @property integer $key
 * @property string $at
 * @property string $created
 */
class Key extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'keys';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['window_id', 'at'], 'required'],
            [['window_id', 'key'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'window_id' => 'Window ID',
            'key'       => 'Key',
            'at'        => 'At',
            'created'   => 'Created',
        ];
    }
}
