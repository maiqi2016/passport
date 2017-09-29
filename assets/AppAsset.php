<?php

namespace passport\assets;

use yii\web\AssetBundle;
use yii;
use yii\web\View;

/**
 * Main passport application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = false;
    public $baseUrl = null;
    public $css = [];
    public $js = [];
    public $depends = [];
    public $jsOptions = [
        'position' => View::POS_END
    ];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->baseUrl = Yii::$app->params['passport_source'];

        $minDirectory = (YII_ENV == 'dev' ? null : '_min');
        $suffix = (YII_ENV == 'dev' ? time() : VERSION);

        $this->css = [
            "node_modules/bootstrap/dist/css/bootstrap.css?version=" . $suffix
        ];
        $this->js = [
            "node_modules/jquery/dist/jquery.min.js?version=" . $suffix,
            "node_modules/angular/angular.min.js?version=" . $suffix,
            "node_modules/alloyfinger/alloy_finger.js?version=" . $suffix,
            "js{$minDirectory}/main.js?version=" . $suffix,
        ];
    }
}
