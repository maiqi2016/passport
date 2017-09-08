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
            'where' => [
                ['id' => $userId],
                ['state' => 1]
            ],
            'select' => [
                'id',
                'username',
                'phone',
                'role',
                'sex',
                'country',
                'province',
                'city',
                'head_img_url'
            ]
        ]);

        exit(json_encode($record, JSON_UNESCAPED_UNICODE));
    }
}