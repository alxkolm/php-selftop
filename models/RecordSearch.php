<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Record;

/**
 * RecordSearch represents the model behind the search form about `app\models\Record`.
 */
class RecordSearch extends Record
{
    public $dateFrom;
    public $dateTo;
    public $timestampFrom;
    public $timestampTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'window_id', 'duration', 'motions', 'motions_filtered', 'clicks', 'keys', 'created'], 'integer'],
            [['dateFrom'], 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'timestampFrom'],
            [['dateTo'], 'date', 'format' => 'yyyy-MM-dd', 'timestampAttribute' => 'timestampTo'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Record::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'window_id' => $this->window_id,
            'duration' => $this->duration,
            'motions' => $this->motions,
            'clicks' => $this->clicks,
            'keys' => $this->keys,
            'created' => $this->created,
        ]);

        $timezone = new \DateTimeZone('UTC');
        if ($this->timestampFrom) {
            $from = (new \DateTime('now', $timezone))->setTimestamp(strtotime('today',$this->timestampFrom))->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start >= :today',
                [':today' => $from->format('Y-m-d H:i:s')]
            );
        }
        if ($this->timestampTo) {
            $to = (new \DateTime('now', $timezone))->setTimestamp(strtotime('today 23:59:59', $this->timestampTo))->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start < :todayNight',
                [':todayNight' => $to->format('Y-m-d H:i:s')]
            );
        }

        return $dataProvider;
    }
}
