<?php
namespace common\models\ActiveRecord;

use \yii\db\ActiveRecord;
use Yii;
use yii\db\Exception;

/**
 * Счет юзера
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $number
 * @property double $amount
 * @property integer $is_default
 *
 * @property User $user
 */
class Account extends ActiveRecord
{
    // админский счет
    const SYSTEM_ACCOUNT_ID = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['amount', 'double', 'min' => 0]
        ];
    }

    /**
     * Создание пустого счёта
     *
     * @param integer $userId
     *
     * @return Account
     */
    public static function createdEmptyAccount($userId)
    {
        $account = new Account();
        $account->user_id = $userId;
        $account->amount = 0;
        // генерируем номер счёта
        $account->number = mt_rand(10000000, 99999999);

        return $account;
    }

    /**
     * Списание со счета отправителя(либо админского, если это операция - начисление)
     *
     * @param User $fromUser
     * @param double $operationAmount
     *
     * @return bool
     */
    protected static function fromAccountDecrease($fromUser, $operationAmount)
    {
        if ($fromUser->is_admin) {
            return Account::decreaseSystem($operationAmount);
        }
        $fromUser->account->amount -= $operationAmount;

        return $fromUser->account->save();
    }

    /**
     * Перевод средств между юзерами или начисление денег
     *
     * @param User $toUser получатель
     * @param User $fromUser отправитель
     * @param double $operationAmount сумма
     * @param integer $userId инициатор
     *
     * @return bool
     */
    public static function transferFunds($toUser, $fromUser, $operationAmount, $userId = null)
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $toUser->account->amount += $operationAmount;
            // если удачно списали и начислили по счетам
            if ($toUser->account->save() && self::fromAccountDecrease($fromUser, $operationAmount)) {
                // тогда логируем операцию
                Operation::transferFunds($toUser, $fromUser, $operationAmount, $userId);
                //завершаем транзакцию
                $transaction->commit();
            } else {
                throw new Exception('Operation failed!');
            }

            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
        }
        return false;
    }

    /**
     * Списание средств с админского счёта
     *
     * @param integer $amount сумма
     *
     * @return bool true если удачно
     */
    public static function decreaseSystem($amount)
    {
        return \Yii::$app->db->createCommand('UPDATE "account" SET amount=amount-:amount WHERE id=:id')
            ->bindValues([':amount' => $amount, ':id' => self::SYSTEM_ACCOUNT_ID])
            ->execute();
    }
}