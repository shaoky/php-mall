<?php 
// 用户
namespace app\comm\controller;
use think\Controller;
// use think\Request;

class Sms extends Controller {
    public function index() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\comm\validate\Sms');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Sms');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}