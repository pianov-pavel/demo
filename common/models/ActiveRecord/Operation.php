<?php

namespace common\models\ActiveRecord;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Класс модели "operation".
 *
 * @property integer $id
 * @property integer $to_user_id
 * @property integer $from_user_id
 * @property double $amount
 * @property double $income_balance
 * @property double $outcome_balance
 * @property integer $is_admin
 * @property integer $user_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $toUser
 * @property User $fromUser
 */
class Operation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operation';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['to_user_id', 'from_user_id', 'amount', 'income_balance', 'outcome_balance', 'user_id'], 'required'],
            [['to_user_id', 'from_user_id', 'is_admin', 'user_id'], 'integer'],
            [['amount', 'income_balance', 'outcome_balance'], 'double'],
            [['to_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['to_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'to_user_id' => 'To User ID',
            'from_user_id' => 'From User ID',
            'amount' => 'Amount',
            'income_balance' => 'Income Balance',
            'outcome_balance' => 'Outcome Balance',
            'is_admin' => 'Is Admin',
            'user_id' => 'User ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getToUser()
    {
        return $this->hasOne(User::className(), ['id' => 'to_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFromUser()
    {
        return $this->hasOne(User::className(), ['id' => 'from_user_id']);
    }

    /**
     * @var User $toUser получатель
     * @var User $fromUser отправитель
     * @var double $amount сумма
     * @var integer $userId инициатор
     *
     * @return bool
     */
    public static function transferFunds($toUser, $fromUser, $amount, $userId = null)
    {
        $operation = new Operation();
        $operation->to_user_id = $toUser->id;
        $operation->from_user_id = $fromUser->id;
        $operation->user_id = $userId ?? $fromUser->id;
        $operation->is_admin = $fromUser->is_admin || $userId;
        $operation->amount = $amount;
        $operation->income_balance = $toUser->account->amount;
        $operation->outcome_balance = $fromUser->is_admin ? 0 : $fromUser->account->amount;

        return $operation->save();
    }

    /**
     * @var integer $userId
     *
     * @return ActiveQuery
     */
    public static function getOperationsForUser($userId)
    {
        return self::find()
            ->where(['to_user_id' => $userId])
            ->orWhere(['from_user_id' => $userId])
            ->orderBy(['created_at' => SORT_DESC]);
    }
}