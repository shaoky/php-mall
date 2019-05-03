<?php
// 用户
namespace app\app\controller;
use think\Controller;
class Login extends Controller {
    public function index() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Login');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('app\h5\model\User');
        $data = $model->login($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function register() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->register($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function referee() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->getRefereeList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function refereeInfo() {
        $params =  input('post.');
        $model = model('app\h5\model\User');
        $data = $model->getRefereeInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    
    public function passwordLogin() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Login.passwordLogin');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('app\h5\model\User');
        $data = $model->passwordLogin($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}
