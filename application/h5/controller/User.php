<?php 
// 用户
namespace app\h5\controller;
use app\h5\controller\ApiCommon;
use think\Controller;

class User extends ApiCommon {
    public function info() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->getUserInfoData($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->updateUserInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function index() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->getUserIndex($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function logout() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->setLogout($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    

}