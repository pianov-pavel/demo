<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 17.09.17
 * Time: 14:54
 */

namespace console\controllers;


use common\models\ActiveRecord\User;
use yii\console\Controller;

class TestController extends Controller
{
    /**
     * генерируем юзеров
     * @return string
     */
    public function actionUsers($baseName = 'test', $baseDomain = 'gmail.com', $password = '123456')
    {
        // id = 1 - первый юзер это админ предопределенный в системе, поэтому бежим с двух
        for ($i = 2; $i < 1000; $i++) {
            $user = new User();
            $user->username = $baseName . '-' . $i;
            $user->email = $baseName . '-' . $i . '@' . $baseDomain;
            $user->password = $password;
            $user->status = User::STATUS_ACTIVE;
            $user->is_admin = 0;

            if(!$user->save()) {
                echo '<pre>';
                print_r($user->errors);
                echo '</pre>';
            }
        }
        return Controller::EXIT_CODE_NORMAL;
    }
}