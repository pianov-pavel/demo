<?php
/**
 * @var $this yii\web\View
 * @var $user \common\models\ActiveRecord\User
 * @var $operation \common\models\ActiveRecord\Operation
 * @var $operations \common\models\ActiveRecord\Operation[]
 * @var $pagination \yii\data\Pagination
 */
$this->title = 'Home';
$formatter = \Yii::$app->formatter;
?>

<h3><?= 'User balance:' . $formatter->asDecimal($user->account->amount, 2); ?></h3>

<div class="site-index">
    <table class="table">
        <thead>
        <tr>
            <th>#Num</th>
            <th>Sender</th>
            <th>Recipient</th>
            <th>Amount</th>
            <th>Outcome Balance</th>
            <th>Income Balance</th>
            <th>Created At</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($operations as $i => $operation):?>
            <tr>
                <th scope="row"><?php echo $pagination->offset + $i;?></th>
                <td><?= $operation->fromUser->username;?></td>
                <td><?= $operation->toUser->username;?></td>
                <td><?= $formatter->asDecimal($operation->amount, 2);?></td>
                <td><?= $formatter->asDecimal($operation->outcome_balance, 2);?></td>
                <td><?= $formatter->asDecimal($operation->income_balance, 2);?></td>
                <td><?= date_format(new DateTime($operation->created_at), 'Y-m-d H:i:s');?></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
<p>
    <?= \yii\helpers\Html::a('Send', ['send', 'id' => $user->id], ['class' => 'btn btn-info']) ?>
</p>

<?= \yii\widgets\LinkPager::widget(['pagination' => $pagination]) ?>