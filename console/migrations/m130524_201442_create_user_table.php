<?php

use yii\db\Migration;

/**
 * Таблица пользователей `user`.
 */
class m130524_201442_create_user_table extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string(50)->notNull()->unique()->comment('имя пользователя'),
            'email' => $this->string(50)->notNull()->unique()->comment('email'),
            'password_reset_token' => $this->string()->comment('сброс пароля'),
            'password_hash' => $this->string()->notNull()->comment('пароль'),
            'auth_key' => $this->string(32),
            'status' => $this->integer()->notNull()->defaultValue(10)->comment('статус'),
            'is_admin' => $this->smallInteger()->notNull()->defaultValue(0)->comment('админ?'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('now()'),
        ], $tableOptions);

        // тестовый админ
        $this->insert('user', [
            'id' => \common\models\ActiveRecord\User::HEAD_ADMIN,
            'username' => 'admin',
            'email' => 'pianov.pavel@gmail.com',
            'password_hash' =>    Yii::$app->security->generatePasswordHash('123456') ,//'$2y$13$AvdEHV6VssSky7/bf48TSuP7GjjvWlwvj1UcoYrQtO4FzEJkPqm2e',
            'status' => 10,
            'is_admin' => 1,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
