<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use passport\assets\AppAsset;

AppAsset::register($this);

$params = \Yii::$app->params;

$controller = \Yii::$app->controller->id;
$action = \Yii::$app->controller->action->id;

$ngApp = empty($params['ng_app']) ? 'kkApp' : $params['ng_app'];
$ngCtl = empty($params['ng_ctrl']) ? null : (' ng-controller="' . $params['ng_ctrl'] . '"');

$title = empty($params['title']) ? $params['app_title'] : $params['title'];
$keywords = empty($params['keywords']) ? $params['app_keywords'] : $params['keywords'];
$description = empty($params['description']) ? $params['app_description'] : $params['description'];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html ng-app="<?= $ngApp ?>" lang="<?= \Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="keywords" content="<?= $keywords ?>">
    <meta name="description" content="<?= $description ?>">
    <?= Html::csrfMetaTags() ?>
    <title><?= $title ?></title>
    <?php $this->head() ?>
</head>

<script type="text/javascript">
    var baseUrl = '<?= $params["passport_url"];?>';
    var requestUrl = '<?= $params["passport_url"];?>/?r=';
</script>

<body<?= $ngCtl ?>>

<!-- Loading -->
<div id="loading" class="kk-animate kk-show hidden">
    <div class="loading-bar loading-bounce kk-animate kk-t2b-show">
        <div class="in"></div>
        <div class="out"></div>
    </div>
</div>

<!-- Message -->
<div id="message" class="kk-animate kk-show hidden">
    <div class="message-bar kk-animate kk-t2b-show">
        <p class="message-box"></p>
    </div>
</div>

<!-- Body -->
<?php $this->beginBody() ?>
<?= $content ?>
<?php $this->endBody() ?>

<?php
$minDirectory = (YII_ENV == 'dev' ? null : '_min');
$suffix = (YII_ENV == 'dev' ? time() : VERSION);

$sourceUrl = $params['passport_source'];
$items = [
    'css',
    'js'
];
foreach ($items as $item) {
    $variable = 'source' . ucfirst($item);
    $register = 'register' . ucfirst($item) . 'File';

    if (is_null($this->context->{$variable}) || 'auto' == $this->context->{$variable}) {
        $source = "/{$item}{$minDirectory}/{$controller}/{$action}.{$item}";
        $this->{$register}($sourceUrl . $source . "?version=" . $suffix);
    } elseif (is_array($this->context->{$variable})) {
        foreach ($this->context->{$variable} as $value) {
            if (strpos($value, '/') === 0) {
                $source = "${sourceUrl}{$value}.{$item}";
            } else if (strpos($value, 'http:') === 0 || strpos($value, 'https:') === 0) {
                $source = $value;
            } else {
                $source = "${sourceUrl}/{$item}{$minDirectory}/{$value}.{$item}";
            }

            $char = strpos($source, '?') !== false ? '&' : '?';
            $this->{$register}($source . $char . "version=" . $suffix);
        }
    }
}
?>
</body>
</html>
<?php $this->endPage() ?>
