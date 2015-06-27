<?php
/**
 * Created by PhpStorm.
 * User: alx
 * Date: 24.06.15
 * Time: 23:50
 */

namespace app\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class WindowSearch extends Window
{
    public $groupBy = 'title';
    public $dateFrom;
    public $dateTo;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['groupBy'], 'in', 'range' => ['title', 'class']],
            [['id', 'created'], 'integer'],
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
        $this->load($params);

        $query = Window::find()
            ->joinWith('records', false)
            ->select([
                '{{window}}.*',
                'SUM(duration) as time',
                'SUM(motions) as motions',
                'SUM(motions_filtered) as motions_filtered',
                'SUM(clicks) as clicks',
                'SUM(keys) as keys',
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'time',
                ],
                'defaultOrder' => [
                    'time' => SORT_DESC
                ]
            ]
        ]);


        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->groupBy('{{window}}.' . $this->groupBy);

        $query->andFilterWhere([
            'created' => $this->created,
        ]);

        if ($this->dateFrom) {
            $from = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($this->dateFrom)->setTimezone(new \DateTimeZone('UTC'));
            $query->andWhere(
                '{{activity}}.created >= :today',
                [':today' => $from->format('Y-m-d H:i:s')]
            );
        }
        if ($this->dateTo) {
            $to = (new \DateTime('now', new \DateTimeZone('UTC')))->setTimestamp($this->dateTo)->setTimezone(new \DateTimeZone('UTC'));
            $query->andWhere(
                '{{activity}}.created < :todayNight',
                [':todayNight' => $to->format('Y-m-d H:i:s')]
            );
        }

        return $dataProvider;
    }
}