<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'auth';
?>

<style>
    h2, h4 {
        text-align: center;
    }
    h2 {
        margin: 100px auto 15px;
    }
</style>

<h2>{{second}}<small>s</small></h2>
<h4 ng-init="goBack('<?= $callback ?>')">Platform Logout ...</h4>