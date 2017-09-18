<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $user \common\models\ActiveRecord\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin([
        'action' => $user->isNewRecord ? ['add-user'] : ['edit-user', 'id' => $user->id],
        'method' => 'post',
    ]); ?>

    <?= $form->field($user, 'username')->textInput() ?>
    <?= $form->field($user, 'email')->textInput() ?>
    <?= $form->field($user, 'password')->passwordInput() ?>
    <?php if ($user->isNewRecord) {
        echo $form->field($user, 'is_admin')->checkbox();
    }?>

    <div class="form-group">
        <?= Html::submitButton($user->isNewRecord ? 'Create' : 'Update', ['class' => $user->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>