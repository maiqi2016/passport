<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'auth';
?>

<div class="login-div">
    <div class="input-group">
        <input type="text" class="form-control input-lg" ng-model="info.phone" placeholder="Phone">
        <span class="input-group-btn"><a class="btn btn-default btn-lg" kk-sms="{{info.phone}}" data-type="3">验证码</a></span>
    </div>
    <br>
    <input type="text" class="form-control input-lg" ng-model="info.captcha" placeholder="Captcha">
    <br>
    <button type="button" class="btn btn-primary btn-lg btn-block" kk-tap="login()">登录</button>
</div>