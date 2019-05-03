<?php 
namespace app\admin\model;
use think\Model;
use think\Db;
use think\facade\Request;
use app\comm\model\Common as Base;

class Common extends Base {
    public function getAdminInfo() {
        $header = Request::instance()->header();
        if (empty($header['authorization'])) {
            return false;
        }
        $authorization = $header['authorization'];
        $user = Db::name('admin')->where('token', $authorization)->field('loginPwd, loginSecret', true)->find();
        header("Access-Control-Allow-Origin: *");
        if (empty($header['authorization'])) {
            exit(json_encode([
                'code' => 401,
                'error' => '请登录账号'
            ], JSON_UNESCAPED_UNICODE));
        }
        
        return $user;
    }

}