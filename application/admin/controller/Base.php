<?php 
// 无需登陆
namespace app\admin\controller;
use think\Controller;
use app\admin\controller\ApiCommon;
// use app\comm\model\Auth;

class Base extends ApiCommon {

    public function login() {
        // $auth = model('Auth');
        // $res = $auth->check("登录",1);
        // var_dump($res);die;
        $adminModel = model('Admin');
        $params =  input('post.');
        $data = $adminModel->getAdmin($params);
        if (!$data) {
            return resultArray(['error' => $adminModel->getError()]);
        }
        $this->adminLog('后台登录成功', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function register() {
        $adminModel = model('Admin');
        $params =  input('post.');
        $data = $adminModel->addAdmin($params);
        if (!$data) {
            return resultArray(['error' => $adminModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}