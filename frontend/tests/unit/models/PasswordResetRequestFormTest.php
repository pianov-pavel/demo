<?php

namespace frontend\tests\unit\models;

use Yii;
use frontend\models\Form\PasswordResetRequestForm;
use common\fixtures\UserFixture as UserFixture;
use common\models\ActiveRecord\User;

class PasswordResetRequestFormTest extends \Codeception\Test\Unit
{
    /**
     * @var \frontend\tests\UnitTester
     */
    protected $tester;


    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::className(),
                'dataFile' => codecept_data_dir() . 'user.php'
            ]
        ]);
    }

    public function testSendMessageWithWrongEmailAddress()
    {
        $model = new \frontend\models\Form\PasswordResetRequestForm();
        $model->email = 'not-existing-email@example.com';
        expect_not($model->sendEmail());
    }

    public function testNotSendEmailsToInactiveUser()
    {
        $user = $this->tester->grabFixture('user', 1);
        $model = new \frontend\models\Form\PasswordResetRequestForm();
        $model->email = $user['email'];
        expect_not($model->sendEmail());
    }

    public function testSendEmailSuccessfully()
    {
        $userFixture = $this->tester->grabFixture('user', 0);
        
        $model = new \frontend\models\Form\PasswordResetRequestForm();
        $model->email = $userFixture['email'];
        $user = \common\models\ActiveRecord\User::findOne(['password_reset_token' => $userFixture['password_reset_token']]);

        expect_that($model->sendEmail());
        expect_that($user->password_reset_token);

        $emailMessage = $this->tester->grabLastSentEmail();
        expect('valid email is sent', $emailMessage)->isInstanceOf('yii\mail\MessageInterface');
        expect($emailMessage->getTo())->hasKey($model->email);
        expect($emailMessage->getFrom())->hasKey(Yii::$app->params['supportEmail']);
    }
}
