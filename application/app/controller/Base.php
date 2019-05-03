<?php
// 用户
namespace app\app\controller;
use think\Controller;
class Base extends Controller {
    public function sms() {
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

}
