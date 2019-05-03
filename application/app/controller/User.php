<?php
// 用户
namespace app\app\controller;
use think\Controller;
class User extends Controller {
    public function info() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->getUserInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->updateUserInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function updatePassword() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->updatePassword($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function index() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->getUserIndex($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function logout() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->setLogout($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    
}
