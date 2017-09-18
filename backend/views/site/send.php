<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $userId integer
 * @var $userSendMoneyForm \common\models\Form\UserSendMoneyForm
 **/

$this->title = 'Send money';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_flash') ?>

<div class="user-send">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['send', 'id' => $userId],
        'method' => 'post',
    ]); ?>

    <?= $form->field($userSendMoneyForm, 'email')->textInput(); ?>
    <?= $form->field($userSendMoneyForm, 'amount')->textInput(); ?>

    <div class="form-group">
        <?= Html::submitButton('Send', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>