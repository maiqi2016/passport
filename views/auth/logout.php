<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'auth';
?>

<h4 style="margin: 100px auto; text-align: center;" ng-init="goBack()">Logout ...</h4>