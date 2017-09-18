<?php if (Yii::$app->session->hasFlash('user-flash')): ?>
    <div class="alert alert-success alert-dismissable">
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <?= Yii::$app->session->getFlash('user-flash') ?>
    </div>
<?php endif; ?>