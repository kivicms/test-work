<?php


namespace medicine\models\search;


use medicine\models\Company;

/**
 * Class CompanySearch
 * @package medicine\models\search
 */
class CompanySearch extends Company
{
    /**
     * @var ActiveQuery
     */
    public $query;

    /**
     * @var int
     */
    public $perPage = 20;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'name', 'slug', 'personId', 'personCount'], 'safe'],
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
        $query = $this->query;
        if (!$query) {
            $query = static::find();
        }

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $this->perPage,
            ],
            'sort' => [
                'defaultOrder' => ['sortOrder' => SORT_ASC],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // фильтруем по id
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['=', 'slug', $this->slug]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        // filter by person name
        if (is_int($this->personId)) { // в personId номер модели персоны

            $query->joinWith(['companyperson' => function ($q) {
                $q->where(['companyperson.personId', $this->personId]);
            }]);

        } elseif ($this->personId) { // в personId часть имени

            $query->joinWith(['person' => function ($q) {

                $q->where(['like', 'person.personId', $this->personId]);

            }])->viatable('companyperson', ['person.id' => 'companyperson.personId']);

        }

        return $dataProvider;
    }
}