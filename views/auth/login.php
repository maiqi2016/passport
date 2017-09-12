<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'auth';
?>

<div class="login-bg">
    <div class="input-group">
        <input class="form-control phone input-lg" ng-model="info.phone" placeholder="Phone Number">
        <span class="input-group-btn">
            <a class="btn btn-default btn-lg captcha-btn" kk-sms="{{info.phone}}" data-type="3">Send</a>
        </span>
    </div>
    <br>
    <input class="form-control captcha input-lg" ng-model="info.captcha" placeholder="Captcha / 4">
    <button class="btn btn-default login-btn btn-lg" kk-tap="login()">SIGN IN</button>
</div>