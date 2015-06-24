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
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
        $query = Window::find()
            ->joinWith('records', false)
            ->groupBy('{{window}}.id')
            ->select([
                '{{window}}.*',
                'SUM(time) as time',
                'SUM(motions) as motions',
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

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'created' => $this->created,
        ]);

        return $dataProvider;
    }
}