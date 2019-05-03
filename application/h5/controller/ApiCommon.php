<?php
// 用户
namespace app\h5\controller;
use think\facade\Request;
use think\Controller;

class ApiCommon extends Controller {


    public function initialize() {
        $header = Request::instance()->header();
        header("Access-Control-Allow-Origin: *");
        if (empty($header['authorization'])) {
            exit(json_encode([
                'code' => 401,
                'error' => '请登录账号'
            ], JSON_UNESCAPED_UNICODE));
        }
        $authorization = $header['authorization'];
        $adminModel = model('app\h5\model\User');
        $data = $adminModel->validationToken($authorization);
        if (!$data) {
            exit(json_encode([
                'code' => 401,
                'error' => $adminModel->getError()
            ], JSON_UNESCAPED_UNICODE));
        }
    }


}
