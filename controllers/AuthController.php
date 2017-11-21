<?php

namespace passport\controllers;

use passport\components\Helper;
use Yii;

/**
 * Auth controller
 */
class AuthController extends MainController
{
    /**
     * 获取 code
     *
     * @access public
     *
     * @param string $client_id
     *
     * @return string
     */
    public function actionCode($client_id)
    {
        $params = $this->params('get', [
            'response_type',
            'client_id',
            'redirect_uri',
            'scope',
            'state'
        ]);

        if (!in_array($params['client_id'], $this->clients())) {
            $this->error(Yii::t('yii', 'Invalid data received for parameter "{param}".', ['param' => 'client_id']));
        }

        if ($uid = Yii::$app->session->get(self::USER)) {
            $url = Helper::httpBuildQuery([
                $params['response_type'] => $this->generateCode($uid, $params['client_id'], $params['redirect_uri']),
                'state' => $params['state']
            ], $params['redirect_uri']);

            return $this->redirect($url);
        }

        $this->sourceCss = ['auth/login'];
        $this->sourceJs = ['auth/login'];

        return $this->render('login');
    }

    /**
     * 通过 code 获取 token
     *
     * @access public
     * @return void
     */
    public function actionToken()
    {
        $post = $this->params('post', [
            'grant_type',
            'code',
            'redirect_uri',
            'client_id'
        ], true);

        $record = $this->validateCode($post['code'], $post['client_id'], $post['redirect_uri']);
        if (is_string($record)) {
            $this->fail([
                'code validate failed {reason}',
                'reason' => $record
            ]);
        }

        exit(json_encode([
            'access_token' => $this->generateToken($post['client_id'], $post['code'], $record['id'], $record['user_id']),
            'token_type' => 'bearer',
            'expires_in' => DAY
        ], JSON_UNESCAPED_UNICODE));
    }

    /**
     * 用户登录
     *
     * @access public
     * @return void
     */
    public function actionAjaxLogin()
    {
        $params = Yii::$app->request->post();
        $params = array_merge($params, [
            'ip' => Yii::$app->request->userIP,
            'type' => 3
        ]);
        $user = $this->service('user.login-check', $params);

        if (is_string($user)) {
            Yii::info(Yii::t('common', $user));
            $this->fail($user);
        }

        // Actions after login
        $uid = $user['id'];
        Yii::$app->session->set(self::USER, $uid);

        $this->service('user.login-log', [
            'id' => $uid,
            'ip' => Yii::$app->request->userIP,
            'type' => 'sso-login'
        ]);

        $this->success(null, '登录成功');
    }

    /**
     * 用户登出
     *
     * @access public
     *
     * @param string $callback
     *
     * @return string
     */
    public function actionLogout($callback)
    {
        Yii::$app->session->removeAll();

        $this->sourceCss = ['main'];
        $this->sourceJs = array_merge(self::$logout, [
            'auth/login'
        ]);

        if (empty($callback)) {
            $this->error('callback url is required');
        }

        return $this->render('logout', compact('callback'));
    }
}