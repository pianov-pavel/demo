<?php
namespace backend\models\Form;

use yii\base\Model;

/**
 * AccountForm форма начисления средств - нужна для валидации суммы `common\models\ActiveRecord\Account`.
 */
class AccountForm extends \common\models\ActiveRecord\Account
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['amount', 'required'],
            ['amount', 'compare', 'compareValue' => 0, 'operator' => '>']
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
}