<?php

use yii\db\Migration;

/**
 * Таблица операций по пользователям `operation`.
 */
class m170915_155810_create_operation_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTable('operation', [
            'id' => $this->primaryKey(),
            'to_user_id' => $this->integer()->notNull()->comment('получатель средств'),
            'from_user_id' => $this->integer()->notNull()->comment('отправитель средств'),
            'amount' => $this->decimal(20,5)->notNull()->defaultValue(0)->comment('сумма'),
            'outcome_balance' => $this->decimal(20,5)->notNull()->defaultValue(0)->comment('остаток на счете отправителя'),
            'income_balance' => $this->decimal(20,5)->notNull()->defaultValue(0)->comment('остаток на счете получателя'),
            'is_admin' => $this->smallInteger()->notNull()->defaultValue(0)->comment('админ перевёл?'),
            'user_id' => $this->integer()->notNull()->comment('инициатор операции(при переводе средств между пользователями админом)'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ]);

        // внешний ключ получателя `user`
        $this->addForeignKey(
            'fk-operation-to_user_id',
            'operation',
            'to_user_id',
            'user',
            'id',
            'CASCADE'
        );

        // внешний ключ отправителя `user`
        $this->addForeignKey(
            'fk-operation-from_user_id',
            'operation',
            'from_user_id',
            'user',
            'id',
            'CASCADE'
        );

        // внешний ключ инициатор операции `user`
        $this->addForeignKey(
            'fk-operation-user_id',
            'operation',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // удаляем fk `user`
        $this->dropForeignKey(
            'fk-operation-user_id',
            'operation'
        );

        // удаляем fk `user`
        $this->dropForeignKey(
            'fk-operation-from_user_id',
            'operation'
        );

        // удаляем fk `user`
        $this->dropForeignKey(
            'fk-operation-to_user_id',
            'operation'
        );

        $this->dropTable('operation');
    }
}
