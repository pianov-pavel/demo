<?php
namespace backend\models\Filter;

use yii\base\Model;
use common\models\ActiveRecord\User;
use yii\db\ActiveQuery;

/**
 * UserFilter форма фильтрации для юзеров `common\models\ActiveRecord\User;`.
 */
class UserFilter extends \common\models\ActiveRecord\User
{
    public $username;
    public $email;
    // диапазон для датысоздания юзера
    public $fromDate;
    public $toDate;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'email', 'created_at', 'updated_at'], 'safe'],
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
     * Условия филтрации по полям
     *
     * @param array $params
     *
     * @return ActiveQuery
     */
    public function filter()
    {
        $query = User::getBalanceQuery();

        if (!$this->validate()) {
            return $query;
        }

        if (isset($this->fromDate) && !empty($this->fromDate)) {
            $query->andFilterWhere(['>', 'created_at', date_format(new \DateTime($this->fromDate),'Y-m-d')]);
        }
        if (isset($this->toDate) && !empty($this->toDate)) {
            $query->andFilterWhere(['<', 'created_at', date_format(new \DateTime($this->toDate),'Y-m-d')]);
        }
        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $query;
    }
}