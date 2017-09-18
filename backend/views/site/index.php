<?php
/**
 * @var $this yii\web\View
 * @var $user \common\models\ActiveRecord\User
 * @var $users \common\models\ActiveRecord\User[]
 * @var $pagination \yii\data\Pagination
 * @var $userFilter \backend\models\Filter\UserFilter
 * @var $form ActiveForm
 */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Admin area';
$formatter = \Yii::$app->formatter;
?>

<div class="user-search">
    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'col-md-3']]
    ]); ?>

    <?= $form->field($userFilter, 'username') ?>
    <?= $form->field($userFilter, 'email') ?>
    <?= $form->field($userFilter, 'fromDate')->widget(yii\jui\DatePicker::className(), ['options' => ['class' => 'form-control']]); ?>
    <?= $form->field($userFilter, 'toDate')->widget(yii\jui\DatePicker::className(), ['options' => ['class' => 'form-control']]); ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Reset', ['index'], ['class' => 'btn btn-info']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

<?= $this->render('_flash') ?>

<div class="site-index">
    <table class="table">
        <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Summ</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $i => $user):?>
            <tr>
                <th scope="row"><?php echo $pagination->offset + $i;?></th>
                <td><?= $user->username;?></td>
                <td><?= $user->email;?></td>
                <td><?= $formatter->asDate($user->created_at, 'YYYY-MM-dd');?></td>
                <td><?= $formatter->asDecimal($user->account->amount,2);?></td>
                <td>
                    <?= Html::a('Edit', ['edit-user', 'id' => $user->id], ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('Fill Up', ['fill-account', 'id' => $user->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('Send', ['send', 'id' => $user->id], ['class' => 'btn btn-info']) ?>
                    <?= Html::a('Operations', ['operations', 'userId' => $user->id], ['class' => 'btn btn-info']) ?>

                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
<p>
    <?= Html::a('Add User', ['add-user'], ['class' => 'btn btn-success']) ?>
</p>

<?= \yii\widgets\LinkPager::widget(['pagination' => $pagination]) ?>