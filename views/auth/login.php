<?php
/* @var $this yii\web\View */

$params = \Yii::$app->params;
\Yii::$app->params['ng_ctrl'] = 'auth';
?>

<marquee class="news">如果在微信端有过数据，建议从公众号菜单进入并完成首次绑定手机号，此后方可在普通浏览器中自由登录，以保证您的数据不会丢失。</marquee>
<div class="login-bg" ng-init="info.extra = '<?= $extra ?>'">
    <div class="input-group">
        <input class="form-control phone input-lg" ng-model="info.phone" placeholder="手机号码">
        <span class="input-group-btn">
            <a class="btn btn-default btn-lg captcha-btn" kk-sms="{{info.phone}}" data-type="3">获取验证码</a>
        </span>
    </div>
    <br>
    <input class="form-control captcha input-lg" ng-model="info.captcha" placeholder="验证码">
    <button class="btn btn-default login-btn btn-lg" kk-tap="login()">登录系统</button>
</div>