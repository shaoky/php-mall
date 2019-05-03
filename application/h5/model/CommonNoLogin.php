<?php
// 用户
namespace app\h5\model;
use think\Model;
use think\Db;
use think\facade\Request;
use app\admin\model\WebConfig;
use app\comm\model\Common as Base;

class CommonNoLogin extends Base {

    public function getViewFrom() {
        $header = Request::instance()->header();
        if (empty($header['from'])) {
            exit(json_encode([
                'code' => 500,
                'error' => '缺少请求头参数:from'
            ], JSON_UNESCAPED_UNICODE));
        }
        $from = $header['from'];
        return $from;
    }

    public function getHeaderParams() {
        $header = Request::instance()->header();
        if (empty($header['from'])) {
            exit(json_encode([
                'code' => 500,
                'error' => '缺少请求头参数:from'
            ], JSON_UNESCAPED_UNICODE));
        }
        $from = $header;
        return $from;
    }

    public function getUserId() {
        $header = Request::instance()->header();
        $authorization = $header['authorization'];
        $user = db('user_token')->where([
            ['token', '=' ,$authorization],
            ['isUse', '=', 1]
        ])->find();
        return $user['userId'];
    }

    public function getUserInfo() {
        $header = Request::instance()->header();
        if (empty($header['authorization'])) {
            return false;
        }
        $authorization = $header['authorization'];
        $user = db('user_token')->alias('a')
        ->join('user b', 'a.userId = b.userId')
        ->where([
            ['a.token', '=', $authorization],
            ['a.isUse', '=', 1],
        ])
        ->field('b.userId, b.userNo, b.superiorId, b.superiorNo, b.loginName, b.userName, b.userPhone, b.lastTime, b.userType, b.userPhoto, b.weixinAccount, b.superiorName')->find();
        if ($user) {
            
        } else {
            header("Access-Control-Allow-Origin: *");
            if (empty($header['authorization'])) {
                return false;
            }
        }
        
        return $user;
    }
    
    public function getUserLevel($userType) {
        // $user1 = db('user')->where('userId', $userId)->select();
        // return $user1;
        $userLevel = Db::name('user_level')->where('userType', $userType)->find();
        return $userLevel;
    }
}
