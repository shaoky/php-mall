<?php 
// 用户
namespace app\h5\controller;
use think\Controller;
class Login extends Controller {
    public function index() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Login.login');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('User');
        $data = $model->login($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function register() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Login.register');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('User');
        $data = $model->register($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function referee() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->getRefereeList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function refereeInfo() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->getRefereeInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    
    public function autoLogin() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->autoLogin($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    

}