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
    public $timestampFrom;
    public $timestampTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['groupBy'], 'in', 'range' => ['title', 'process_id']],
            [['id', 'created'], 'integer'],
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
        $this->load($params);

        $query = Window::find()
            ->joinWith('records', false)
            ->select([
                '{{window}}.*',
                'SUM(duration) as time',
                'SUM(motions) as motions',
                'SUM(motions_filtered) as motions_filtered',
                'SUM(clicks) as clicks',
                'SUM(scrolls) as scrolls',
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

        $timezone = new \DateTimeZone(\Yii::$app->timeZone);
        if ($this->timestampFrom) {
            $from = (new \DateTime('@'.strtotime('today',$this->timestampFrom)))->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start >= :today',
                [':today' => $from->format('Y-m-d H:i:s')]
            );
        }
        if ($this->timestampTo) {
            $to = (new \DateTime('@'.strtotime('tomorrow', $this->timestampTo)))->setTimezone($timezone);
            $query->andWhere(
                '{{record}}.start < :todayNight',
                [':todayNight' => $to->format('Y-m-d H:i:s')]
            );
        }

        return $dataProvider;
    }
}