<?php
namespace frontend\controllers;

use common\models\ActiveRecord\Account;
use common\models\Form\UserSendMoneyForm;
use common\models\ActiveRecord\Operation;
use common\models\ActiveRecord\User;
use Yii;
use yii\base\InvalidParamException;
use yii\data\Pagination;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\Form\LoginForm;
use frontend\models\Form\PasswordResetRequestForm;
use frontend\models\Form\ResetPasswordForm;
use frontend\models\Form\SignupForm;
use frontend\models\Form\ContactForm;
use yii\web\NotFoundHttpException;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['signup', 'login', 'request-password-reset'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Главная - Список операций пользователя
     *
     * @return mixed
     */
    public function actionIndex()
    {
        /** @var User $user */
        $user = \Yii::$app->user->identity;
        $operations = Operation::getOperationsForUser($user->id);

        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $operations->count(),
        ]);

        $operations = $operations->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'operations' => $operations,
            'user' => $user,
            'pagination' => $pagination
        ]);
    }

    /**
     * Отправка средств между пользователями
     * @param integer $id юзера, которому посылаем деньги
     *
     * @return mixed
     */
    public function actionSend($id)
    {
        $form = new UserSendMoneyForm();
        $form->senderId = $id;
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            // здесь начисляем средства и создаем проводку
            if(Account::transferFunds(
                $this->findUser('email', $form->email),
                $this->findUser('id', $id),
                $form->amount,
                Yii::$app->user->identity->id)
            ) {
                Yii::$app->session->setFlash('user-flash', 'Operation completed!');
                $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('user-flash', 'Operation failed!');
            }
        }

        return $this->render('send', [
            'userId' => $id,
            'userSendMoneyForm' => $form
        ]);
    }

    /**
     * Достаем юзера из базы
     *
     * @param string $field поле по которому ищем
     * @param string $value и его значение
     * @return User модель
     * @throws NotFoundHttpException если юзер не найден
     */
    protected function findUser($field, $value)
    {
        if (($user = User::findOne([$field => $value])) !== null) {
            return $user;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
