<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

/**
 * 图片上传
 */

// Route::post('/common/image/add', 'common/Image/add'); // 注册
Route::group('common', [
    // 短信
    '/sms' => 'comm/Sms/index',
    /**
     * 地区
     */
    'region/all' => 'comm/Region/all', // 获取全部数据，如果带id，只获取下一级
    'region/city' => 'comm/Region/city', // 获取全部数据，如果带id，只获取下一级
    'alipay/generateSign' => 'comm/Alipay/generateSign',
    'alipay/payUrlAlipay' => 'comm/Alipay/payUrlAlipay',
    'alipay/checkRsaSign' => 'comm/Alipay/checkRsaSign',
    'alipay/returnUrl' => 'comm/Alipay/returnUrl',
    /**
     * 图片
     */
    'image/add' => 'comm/Image/add'

])
->header('Access-Control-Allow-Origin','*')
->allowCrossDomain();

return [

];
