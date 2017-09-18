<?php

namespace passport\controllers;

/**
 * User controller
 */
class UserController extends MainController
{
    /**
     * 获取用户信息
     *
     * @access public
     * @return void
     */
    public function actionInfo()
    {
        $userId = $this->validate();

        $record = $this->service('user.detail', [
            'where' => [['id' => $userId]]
        ]);

        if (!empty($record) && !$record['state']) {
            exit(json_encode('the account has been frozen'));
        }

        exit(json_encode($record, JSON_UNESCAPED_UNICODE));
    }
}