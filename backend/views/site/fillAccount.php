<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var $this yii\web\View
 * @var $id integer
 * @var $accountForm \backend\models\Form\AccountForm
 **/

$this->title = 'Fill balance';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?= $this->render('_flash') ?>

<div class="user-fill-balance">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin([
        'action' => ['fill-account', 'id' => $id],
        'method' => 'post',
    ]); ?>

    <?= $form->field($accountForm, 'amount'); ?>

    <div class="form-group">
        <?= Html::submitButton('Fill', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>