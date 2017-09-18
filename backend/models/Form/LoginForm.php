<?php
namespace backend\models\Form;

use Yii;

/**
 * Login form - переопределили форму из common для контроля доступа в backend'е
 */
class LoginForm extends \common\models\Form\LoginForm
{
    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        // проверяем админский ли аккаунт
        if ($this->getUser()->is_admin && $this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        } else {
            return false;
        }
    }
}
