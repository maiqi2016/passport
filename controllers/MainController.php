<?php

namespace passport\controllers;

use passport\components\Helper;
use yii;
use yii\web\Controller;
use yii\helpers\Html;

/**
 * Main controller
 */
class MainController extends Controller
{
    /**
     * @cont integer
     */
    const USER = 'sso_user_id';

    /**
     * @var mixed 前端 CSS 资源
     * @example false, null/auto
     */
    public $sourceCss = false;

    /**
     * @var mixed 前端 JS 资源
     * @example false, null/auto
     */
    public $sourceJs = false;

    /**
     * @var array 域名白名单
     */
    public static $logout = [
        'http://www.kakehotels.com/?r=user/logout'
    ];

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->enableCsrfValidation = false;
    }

    /**
     * 客户端白名单
     *
     * @access protected
     * @return array
     */
    protected function clients()
    {
        $client = [];
        foreach (self::$logout as $item) {
            $client[] = md5(strrev(parse_url($item, PHP_URL_HOST)));
        }

        return $client;
    }

    /**
     * Parse error message
     *
     * @access private
     * @return array
     */
    protected function parseError()
    {
        if (null === ($exception = Yii::$app->getErrorHandler()->exception)) {
            $exception = new yii\web\HttpException(400, Yii::t('yii', 'An internal server error occurred.'));
        }

        if ($exception instanceof yii\web\HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = $exception->getCode();
        }

        if ($exception instanceof yii\base\Exception) {
            $name = $exception->getName();
        } else {
            $name = Yii::t('yii', 'Error');
        }
        if ($code) {
            $name .= " (#$code)";
        }

        if ($exception instanceof yii\base\UserException) {
            $message = $exception->getMessage();
        } else {
            $message = Yii::t('yii', 'An internal server error occurred.');
        }

        return [
            'code' => $code,
            'title' => $name,
            'message' => $message,
            'exception' => $exception
        ];
    }

    /**
     * 显示错误提示页面
     *
     * @access public
     *
     * @param string  $message
     * @param integer $code
     * @param string  $trace
     *
     * @return void
     */
    public function error($message, $code = null, $trace = null)
    {
        $this->sourceCss = [
            'message/index'
        ];

        switch ($code) {
            case '404' :
                $params = [
                    'type' => '404',
                    'message' => Yii::t('common', 'page not found'),
                    'title' => '404'
                ];
                break;

            default :
                $params = [
                    'type' => 'error',
                    'message' => $message,
                    'title' => 'Oops!'
                ];
                break;
        }

        Yii::error('catch error : ' . json_encode($params, JSON_UNESCAPED_UNICODE) . ' ' . $trace);

        $content = $this->renderFile(Yii::$app->getViewPath() . DS . 'message.php', $params);
        $content = $this->renderContent($content);

        exit($content);
    }

    /**
     * 公共错误控制器
     *
     * @access public
     * @auth-pass-all
     *
     * @param string  $message
     * @param integer $code
     * @param string  $title
     *
     * @return void
     */
    public function actionError($message = null, $code = 400, $title = 'Error')
    {
        if (!$message) {
            /**
             * @var $code      integer
             * @var $title     string
             * @var $message   string
             * @var $exception object
             */
            $error = $this->parseError();

            extract($error);
            $trace = YII_DEBUG ? strval($exception->getPrevious()) : null;
        } else {
            $trace = null;
        }

        if (Yii::$app->request->isAjax) {
            $this->fail($title . ':' . $message);
        }

        $this->error($message, $code, $trace);
    }

    /**
     * Ajax 发送手机验证码
     *
     * @access public
     * @return void
     */
    public function actionAjaxSms()
    {
        $phone = Yii::$app->request->post('phone');
        if (!preg_match('/^[\d]([\d\-\ ]+)?[\d]$/', $phone)) {
            $this->fail('Phone number illegal');
        }

        $result = $this->service('phone-captcha.send', [
            'phone' => $phone,
            'type' => Yii::$app->request->post('type')
        ]);

        if (is_string($result)) {
            Yii::error('Sms error: ' . $result);
            $this->fail('Phone captcha send fail');
        }

        $this->success(null, 'Phone captcha send success');
    }

    /**
     * Call service
     *
     * @access public
     *
     * @param string $api
     * @param array  $params
     * @param string $cache
     * @param string $project
     * @param string $lang
     *
     * @return mixed
     * @throws \Exception
     */
    public function service($api, $params = [], $cache = 'no', $project = PROJECT, $lang = 'zh-CN')
    {
        $conf = Yii::$app->params;

        // array to string
        array_walk($params, function (&$value) {
            if (is_array($value)) {
                $value = json_encode($value);
            } else if (is_numeric($value)) {
                $value = (string) $value;
            } else if (is_bool($value)) {
                $value = (string) ($value ? 1 : 0);
            } else if (!is_string($value)) {
                $value = null;
            }
        });

        // merge params
        $api = $project . '.' . $api;
        $params = array_merge($params, [
            'app_api' => $api,
            'app_id' => $conf['service_app_id'],
            'app_secret' => $conf['service_app_secret'],
            'app_lang' => $lang,
            'app_cache' => $cache
        ]);

        // create sign
        unset($params['r']);
        $params = Helper::createSign($params);
        $params = '"' . http_build_query($params) . '"';

        // call client
        $client = realpath(Yii::getAlias('@thrift/client.php'));
        Yii::trace('服务请求开始: ' . $api . ' with ' . json_encode($params));
        $cmd = Helper::joinString(' ', 'php', $client, $params, $conf['thrift_ip'], $conf['thrift_port']);
        exec($cmd, $result);
        Yii::trace('服务请求结束');

        $result = Helper::handleCliResult($result);

        if ($result['state'] == -1) {
            if (empty($result['info'])) {
                $result['info'] = '接口未返回任何数据';
            }
            Yii::error($result['info']);
            if (strpos($result['info'], '<!doctype html>') === false) {
                throw new \Exception($result['info']);
            }
            exit($result['info']);
        }

        if ($result['info'] == 'DEBUG') {
            $this->dump($result['data']);
        }

        return $result['state'] ? $result['data'] : $result['info'];
    }

    /**
     * Dump variable
     *
     * @param mixed $var
     * @param bool  $strict
     * @param bool  $exit
     *
     * @return void
     */
    public function dump($var, $strict = false, $exit = true)
    {
        Helper::dump($var, $exit, $strict);
    }

    /**
     * 语言包翻译 - 支持多个语言包
     *
     * @access public
     *
     * @param mixed  $lang
     * @param string $package
     *
     * @return string
     */
    public function lang($lang, $package = 'common')
    {
        if (is_string($lang)) {
            return Yii::t($package, $lang);
        }

        if (!is_array($lang)) {
            return null;
        }

        if (is_array(current($lang))) {
            $text = null;
            foreach ($lang as $_lang) {
                $text .= $this->lang($_lang, $package);
            }

            return $text;
        }

        $params = $lang;
        $lang = array_shift($params);

        return Yii::t($package, $lang, $params);
    }

    /**
     * 返回成功提示信息及数据
     *
     * @access public
     *
     * @param mixed  $data    返回数据
     * @param mixed  $lang    成功提示信息
     * @param string $package 语言包
     *
     * @return void
     */
    public function success($data = [], $lang = null, $package = 'common')
    {
        $info = $this->lang($lang, $package);
        $info && Yii::trace($info);

        exit(json_encode([
            'state' => 1,
            'info' => $info,
            'data' => $data
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * 返回失败提示信息
     *
     * @access public
     *
     * @param mixed   $lang    成功提示信息
     * @param string  $package 语言包
     * @param integer $state   状态码
     *
     * @return void
     */
    public function fail($lang, $package = 'common', $state = 0)
    {
        $info = $this->lang($lang, $package);
        Yii::info($info);

        exit(json_encode([
            'state' => $state,
            'info' => $info,
            'data' => null
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * 获取 get 参数
     *
     * @access public
     *
     * @param string  $method
     * @param array   $required
     * @param boolean $ajaxResponse
     *
     * @return array
     */
    public function params($method = 'get', $required = [], $ajaxResponse = false)
    {
        $method = strtolower($method);
        $params = Yii::$app->request->{$method}();
        unset($params['r']);

        if (!empty($params['redirect_uri'])) {
            $params['redirect_uri'] = urldecode($params['redirect_uri']);
        }

        foreach ($required as $item) {
            if (empty($params[$item])) {
                if ($ajaxResponse) {
                    $this->fail([
                        'Missing required parameters: {params}',
                        'params' => $item
                    ], 'yii');
                }
                $this->error(Yii::t('yii', 'Missing required parameters: {params}', ['params' => $item]));
            }
        }

        return $params;
    }

    /**
     * 获取引用地址
     *
     * @return mixed
     */
    public function reference()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
    }

    /**
     * 生成签名
     *
     * @access protected
     *
     * @param array ...$args
     *
     * @return string
     */
    protected function generateSign(...$args)
    {
        $args = implode(',', $args);
        $args = strrev($args);

        return md5($args);
    }

    /**
     * 生成 code
     *
     * @access protected
     *
     * @param integer $userId
     * @param string  $clientId
     * @param string  $redirectUri
     *
     * @return string
     */
    protected function generateCode($userId, $clientId, $redirectUri)
    {
        $code = Helper::generateToken(16, 36);

        $result = $this->service('general.newly-or-edit', [
            'table' => 'sso_code',
            'where' => ['user_id' => $userId],
            'code' => $code,
            'sign' => $this->generateSign($clientId, $redirectUri),
            'state' => 1
        ]);

        if (is_string($result)) {
            $this->error(Yii::t('common', $result));
        }

        return $code;
    }

    /**
     * 验证 code
     *
     * @access protected
     *
     * @param string $code
     * @param string $clientId
     * @param string $redirectUri
     *
     * @return mixed
     */
    protected function validateCode($code, $clientId, $redirectUri)
    {
        $record = $this->service('general.detail', [
            'table' => 'sso_code',
            'where' => [
                ['code' => $code],
                ['state' => 1]
            ]
        ]);

        if (empty($record)) {
            return 'illegal code';
        }

        if (strtotime($record['update_time']) + MINUTE < TIME) {
            return 'already expire';
        }

        $sign = $record['generate_sign'] = $this->generateSign($clientId, $redirectUri);
        if ($record['sign'] !== $sign) {
            return 'sign error';
        }

        return $record;
    }

    /**
     * 生成 token
     *
     * @access protected
     *
     * @param string  $clientId
     * @param string  $code
     * @param integer $ssoCodeId
     * @param integer $userId
     *
     * @return string
     */
    protected function generateToken($clientId, $code, $ssoCodeId, $userId)
    {
        $token = Yii::$app->rsa->encryptByPublicKey($userId . '-' . $code);

        $result = $this->service('general.newly-sso-token', [
            'sso_code_id' => $ssoCodeId,
            'token' => $token,
            'sign' => $this->generateSign($clientId, $ssoCodeId, $userId)
        ]);

        if (is_string($result)) {
            $this->fail($result);
        }

        $token = str_replace('/', '_', $token);
        $token = str_replace('+', '/', $token);
        $token = str_replace('-', '+', $token);

        return $token;
    }

    /**
     * 验证 token
     *
     * @access protected
     *
     * @param string $token
     * @param string $clientId
     *
     * @return mixed
     */
    protected function validateToken($token, $clientId)
    {
        $token = str_replace('+', '-', $token);
        $token = str_replace('/', '+', $token);
        $token = str_replace('_', '/', $token);

        $token = str_replace(' ', '+', $token);

        $tokenStr = Yii::$app->rsa->decryptByPrivateKey($token);
        if (empty($tokenStr)) {
            return 'illegal token string';
        }

        list($userId, $code) = explode('-', $tokenStr);

        $record = $this->service('general.detail', [
            'table' => 'sso_token',
            'join' => [
                ['table' => 'sso_code']
            ],
            'where' => [
                ['sso_code.code' => $code],
                ['sso_code.user_id' => $userId],
                ['sso_token.token' => $token],
                ['sso_token.state' => 1]
            ],
            'select' => [
                'sso_code.user_id',
                'sso_token.sso_code_id',
                'sso_token.sign',
                'sso_token.add_time'
            ]
        ]);

        if (empty($record)) {
            return 'illegal token';
        }

        if (strtotime($record['add_time']) + WEEK < TIME) {
            return 'already expire';
        }

        $sign = $this->generateSign($clientId, $record['sso_code_id'], $userId);
        if ($sign !== $record['sign']) {
            return 'sign error';
        }

        return $record['user_id'];
    }

    /**
     * 接口前置验证
     *
     * @return mixed
     */
    protected function validate()
    {
        $post = $this->params('post', [
            'token',
            'client_id'
        ], true);

        $result = $this->validateToken($post['token'], $post['client_id']);

        if (is_numeric($result)) {
            return $result;
        }

        if (is_string($result)) {
            $this->fail([
                'Token validate failed {reason}',
                'reason' => $result
            ]);
        }

        return null;
    }
}
