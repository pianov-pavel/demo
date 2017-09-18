<?php
namespace backend\controllers;

use backend\models\Form\AccountForm;
use backend\models\Filter\UserFilter;
use common\models\Form\UserSendMoneyForm;
use common\models\ActiveRecord\Account;
use common\models\ActiveRecord\Operation;
use common\models\ActiveRecord\User;
use Yii;
use yii\data\Pagination;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use backend\models\Form\LoginForm;
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
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
        ];
    }

    /**
     * Login action.
     *
     * @return string
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
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Список пользователей
     *
     * @return string
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->get('UserFilter');
        $userFilter = new UserFilter($params);
        $users = $userFilter->filter();
        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $users->count(),
        ]);

        $users = $users->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('index', [
            'users' => $users,
            'pagination' => $pagination,
            'userFilter' => $userFilter
        ]);
    }

    /**
     * Создание юзера
     *
     * @return mixed
     */
    public function actionAddUser()
    {
        $user = new User();
        $user->attributes = \Yii::$app->request->post('User');
        if (\Yii::$app->request->isPost && $user->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('addUser', [
                'user' => $user,
            ]);
        }
    }

    /**
     * Редактирование пользователя
     *
     * @param integer $id
     * @return mixed
     */
    public function actionEditUser($id)
    {
        $params = Yii::$app->request->post();
        $user = $this->findUser('id', $id);
        $user['password'] = $params ? $params['User']['password'] : '';
        if ($user->load($params) && $user->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('editUser', [
                'user' => $user,
            ]);
        }
    }

    /**
     * Пополнение счёта
     * @param integer $id юзера, которому посылаем деньги
     *
     * @return mixed
     */
    public function actionFillAccount($id)
    {
        $form = new AccountForm();
        // здесь начисляем средства и создаем проводку
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            if(Account::transferFunds($this->findUser('id', $id), Yii::$app->user->identity, $form->amount)) {
                Yii::$app->session->setFlash('user-flash', 'Operation completed!');
                $this->redirect(['index']);
            } else {
                Yii::$app->session->setFlash('user-flash', 'Operation failed!');
            }
        }

        return $this->render('fillAccount', [
            'id' => $id,
            'accountForm' => $form,
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
        // здесь начисляем средства и создаем проводку
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
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
     * Список операций пользователя
     *
     * @param integer $userId
     *
     * @return mixed
     */
    public function actionOperations($userId)
    {
        $user = $this->findUser('id', $userId);
        $operations = Operation::getOperationsForUser($userId);;

        $pagination = new Pagination([
            'defaultPageSize' => 5,
            'totalCount' => $operations->count(),
        ]);

        $operations = $operations->offset($pagination->offset)
            ->limit($pagination->limit)
            ->all();

        return $this->render('operations', [
            'operations' => $operations,
            'user' => $user,
            'pagination' => $pagination
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
