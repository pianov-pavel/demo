<?php

use yii\db\Migration;

/**
 * Таблица `account`.
 */
class m170906_142911_create_account_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('account', [
            'id' => $this->primaryKey(),
            'number' => $this->integer(8)->notNull()->unique()->comment('номер счета'),
            'user_id' => $this->integer()->notNull()->comment('внешний ключ юзера'),
            'amount' => $this->decimal(20,5)->notNull()->defaultValue(0)->comment('сумма')
        ]);

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-account-user_id',
            'account',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // админский счет
        $this->insert('{{%account}}', [
            'number' => \common\models\ActiveRecord\Account::SYSTEM_ACCOUNT_ID,
            'user_id' => \common\models\ActiveRecord\User::HEAD_ADMIN,
            'amount' => 0
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // удаляем fk `user`
        $this->dropForeignKey(
            'fk-account-user_id',
            'account'
        );

        $this->dropTable('account');
    }
}
