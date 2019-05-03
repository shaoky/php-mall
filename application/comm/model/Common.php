<?php
// 用户
namespace app\comm\model;
use think\Model;
use think\Db;
use think\facade\Request;

class Common extends Model {

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

    /**
     * 获取token
     * 主要用于没有token的时候，通过userId去查找
     */
    public function getTokenArray($userId) {
        $list = Db::name('user_token')->where([
            ['userId', '=', $userId],
            ['isUse', '=', 1]
        ])->select();
        $arr = [];
        foreach ($list as $item) {
            $arr[] = $item['token'];
        }
        return $arr;
    }
    public function getHeaderParams() {
        $header = Request::instance()->header();
        if (empty($header['from'])) {
            exit(json_encode([
                'code' => 500,
                'error' => '缺少请求头参数:from'
            ], JSON_UNESCAPED_UNICODE));
        }
        if (empty($header['app'])) { 
            $header['app'] = 1;
        }
        return $header;
    }
}
