<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/3
 * Time: 11:25
 */
namespace app\app\controller;
use think\Controller;
use think\Db;
/**
 * @apiDefine appUserLogin 买手APP-登录
 */
class Login extends ApiCommon {

    public function login (){
        $params = input('post.');
        $userModel = model('user');
        $token = $this->request->token('__token__', 'jsjh_mall');
        $obj = $userModel->login($params,$token);
        if (!$obj) {
            return resultArray(['error' => $userModel->getError()]);
        }
        return resultArray(['obj' => $obj]);
    }

    public function queryInfo (){
        $params = input('post.');
        $userModel = model('user');
        $obj = $userModel->queryInfo($params);
        if (!$obj){
            return resultArray(['error' => $userModel->getError()]);
        }
        return resultArray(['obj' => $obj]);
    }

    public function bindrelation () {
        $params = input('post.');
        $userModel = model('user');
        $res = $userModel->bindrelation($params);
        if (!$res) {
            return resultArray(['error'=>$userModel->getError()]);
        }
        return resultArray(['obj'=>$res]);
    }
}