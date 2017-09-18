<?php
namespace common\models\Form;

use common\models\ActiveRecord\User;
use yii\base\Model;

/**
 * UserSendMoneyForm форма отправки средств между пользователями
 *
 * @property integer $senderId
 * @property integer $amount
 */
class UserSendMoneyForm extends User
{
    public $senderId; // id отправителя
    public $amount;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'amount'], 'required'],
            ['email', 'email'],
            [['email'], 'exist', 'targetClass' => User::className(), 'targetAttribute' => ['email' => 'email']],
            ['amount', 'compare', 'compareValue' => 0, 'operator' => '>'],
            ['email', 'validateBalance']

        ];
    }

    /**
     * Проверка что баланс отправителя "платёжеспособен"
     *
     * @param string $attribute
     */
    public function validateBalance($attribute)
    {
        $user = User::findOne($this->senderId);
        if (!$user) {
            $this->addError($attribute, 'User not found!');
        }
        if ($user->account->amount - $this->amount < 0) {
            $this->addError($attribute, 'This user doesn\'t have enough money for operation!');
        }
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