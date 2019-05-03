<?php
// header("content-type:text/html;charset=utf-8");
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 返回对象
 * @param $array 响应数据
 */
function resultArray($array) {
    // $ip = get_client_ip();
    // $ipList = ['115.219.253.53'];
    // if (in_array($ip, $ipList) == 0) {
    //     return json_encode([
    //         'code' => 500,
    //         'data' => '',
    //         'error' => '服务器正在维护中'
    //     ], JSON_UNESCAPED_SLASHES);
    // }
    if (isset($array['data'])) {
        $array['error'] = '';
        $code = 200;
    } elseif (isset($array['error'])) {
        $code = 500;
        $array['data'] = null;
    }
    return json_encode([
        'code'  => $code,
        'data'  => $array['data'],
        'error' => $array['error']
    ], JSON_UNESCAPED_SLASHES);
    // JSON_UNESCAPED_SLASHES
    // JSON_UNESCAPED_UNICODE
}

/**
 * 请求接口
 * @param 
 */
function curl_request($url, $method = 'GET', $params = []) {
    $ch = curl_init();                                      //初始化curl
    if ($method == 'GET') {
        curl_setopt($ch, CURLOPT_URL, $url);                    //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);                    //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);            //要求结果为字符串且输出到屏幕上
        // curl_setopt($ch, CURLOPT_HTTPHEADER,$header);           // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);                                 //运行curl
        curl_close($ch);
    }
    
    return json_decode($data);
}


/**
 * 用户密码加密方法
 * @param  string $str      加密的字符串
 * @param  [type] $auth_key 加密符
 * @return string           加密后长度为32的字符串
 */
function user_md5($str, $auth_key = '') {
    return '' === $str ? '' : md5(sha1($str) . $auth_key);
}

/**
 * 图片上传
 * 备注：待优化-缩略图，文件大小限制等等
 */
function imageUpload() {
    $file = request()->file('file');
    $info = $file->move(Env::get('root_path') . 'public/upload');
    $imageUrl = DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . $info->getSaveName();
    $data['imageUrl'] = $imageUrl;
    return $data;
}

/**
 * 微信日志
 */
function output_log_file($str,$type="alipay", $id=1)
    {
        $date = date('Y-m-d');
        if (PHP_OS == 'Linux') {
            $path = "/var/log";
            $filename = $path . '/' . "weixin.log";
        } else {
            $path = DOCROOT . "logs\\$type\\$date";
            $filename = $path . '\\' . "weixin.log";
        }
        $files = fopen($filename, 'a');
        fwrite($files, "\r\n".$str);
        fclose($files);
    }

/**
 * 状态名称管理
 * @param string name 状态名称
 * @param number status 状态的值
 */
function getStatusName($name, $status) {
    // 是否开启，转化true和false
    if ($name == 'isOpen') {
        if ($status == 0) {
            return false;
        }
        if ($status == 1) {
            return true;
        }
    }
    // 支付方式
    if ($name == 'payType') {
        if ($status == 1) {
            return '支付宝';
        }
        if ($status == 2) {
            return '微信';
        }
    }
    // 订单来源
    if ($name == 'orderFrom') {
        if ($status == 1) {
            return 'h5';
        }
        if ($status == 2) {
            return 'Android';
        }
        if ($status == 3) {
            return 'IOS';
        }
    }
    // 订单状态
    if ($name == 'orderStatus') {
        if ($status == 1) {
            return '待付款';
        }
        if ($status == 2) {
            return '待发货';
        }
        if ($status == 3) {
            return '已发货';
        }
        if ($status == 4) {
            return '交易完成';
        }
        if ($status == 5) {
            return '退款中';
        }
        if ($status == 6) {
            return '已退款';
        }
        if ($status == 7) {
            return '已取消';
        }
    }
    // 退款状态
    if ($name == 'refundStatus') {
        if ($status == 1) {
            return '退款中';
        }
        if ($status == 2) {
            return '已退款';
        }
        if ($status == 3) {
            return '已拒绝';
        }
    }
    // 商品属性
    if ($name == 'attrType') {
        if ($status == 1) {
            return '手工录入';
        }
        if ($status == 2) {
            return '单选属性';
        }
        if ($status == 3) {
            return '多选属性';
        }
    }
    // 商品属性
    if ($name == 'specType') {
        if ($status == 1) {
            return '唯一属性';
        }
        if ($status == 2) {
            return '单选属性';
        }
        if ($status == 3) {
            return '多选属性';
        }
    }
    // 提现类型
    if ($name == 'withdrawalTypeName') {
        switch ($status){
            case 1 :{
                return '微信';
            }
            case 2 :{
                return '支付宝';
            }
            case 3 :{
                return '银行卡';
            }
        }
    }

    if ($name == 'UserWithdrawalPositionStatus')
    {
        switch ($status){
            case 1 :{
                return '处理中';
            }
            case 2 :{
                return '已完成';
            }
            case 3 :{
                return '已拒绝';
            }
        }
    }
    if ($name == 'refundStatus')
    {
        switch ($status){
            case 1 :{
                return '退款中';
            }
            case 2 :{
                return '已退款';
            }
            case 3 :{
                return '已拒绝';
            }
        }
    }

    if ($name == 'refundType')
    {
        switch ($status){
            case 1 :{
                return '仅退款';
            }
            case 2 :{
                return '退货退款';
            }
        }
    }

    if ($name == 'commissionStatus') {
        switch ($status){
            case 1 :{
                return '未结算';
            }
            case 2 :{
                return '已结算';
            }
            case 3 :{
                return '已退款';
            }
        }
    }

    if ($name == 'userType') {
        switch ($status){
            case 2 :{
                return '黄金会员';
            }
            case 3 :{
                return '铂金舵手';
            }
            case 4 :{
                return '钻石舵手';
            }
        }
    }

    if ($name == 'couRangeKey') {
        switch ($status){
            case 1 :{
                return '全场';
            }
            case 2 :{
                return '分类';
            }
            case 3 :{
                return '单品';
            }
            case 4 :{
                return '品牌';
            }
        }
    }

    if ($name == 'shopStatus') {
        switch ($status){
            case 0 :{
                return '休息中';
            }
            case 1 :{
                return '营业中';
            }
        }
    }
}
/**
 * 正则替换特殊字符
 * @param string str 替换字符串
 */
function replaceSpecialChar($str){
    $regex = "/\ |\/|\~|\!|\@|\#|\\$|\%|\^|\&|\*|\(|\)|\_|\+|\{|\}|\:|\<|\>|\?|\[|\]|\,|\.|\/|\;|\'|\·|\`|\-|\=|\\\|\|/";
    return preg_replace($regex,"",$str);
}
 
/**
 * 获取ip
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}