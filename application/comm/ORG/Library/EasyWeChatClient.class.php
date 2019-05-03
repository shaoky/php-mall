<?php

namespace Common\ORG\Library;

use Common\Core\Constant;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\LuckyMoney\API;
use EasyWeChat\Payment\Merchant;
use EasyWeChat\Payment\Order;
use EasyWeChat\Payment\Payment;

/**
 * 微信支付SDK
 * Class EasyWeChatClient
 * @package library
 */
class EasyWeChatClient
{

    private $app;
    private $type;

    private $appid;
    private $mchid;
    private $key;
    private $sslcert_path;
    private $sslkey_path;
    private $notify_url;
    private $appsecret;
    private $oauthCallback;
    private $options;

    const WX_SDK_TYPE_WAP = '1';//公众号支付
    const WX_SDK_TYPE_APP = '2';//APP支付
    const WX_SDK_TYPE_YM9 = '3';//易买酒公众号支付
    const WX_SDK_TYPE_YM9_APP = '4';//易买酒APP支付
    const WX_SDK_TYPE_CLOUD_APP = '5';//酒商云APP支付
    const WX_SDK_TYPE_QIAN_WAP = '6';//钱哥公众号支付

    const WX_SDK_TYPE = array(
        self::WX_SDK_TYPE_WAP => 'JS_PAY',//公众号支付
        self::WX_SDK_TYPE_APP => 'APP_PAY',//APP支付
        self::WX_SDK_TYPE_YM9 => 'YM9_JS_PAY',//易买酒JS支付
        self::WX_SDK_TYPE_YM9_APP => 'YM9_APP_PAY',//易买酒APP支付
        self::WX_SDK_TYPE_CLOUD_APP => 'CLOUD_APP_PAY',//酒商云APP支付
        self::WX_SDK_TYPE_QIAN_WAP => 'QIAN_PAY'//钱哥公众号支付
    );

    const WX_PAY_TRADE_TYPE_JS_API = 'JSAPI';//公众号支付
    const WX_PAY_TRADE_TYPE_NATIVE = 'NATIVE';//原生扫码支付
    const WX_PAY_TRADE_TYPE_APP = 'APP';//app支付
    const WX_PAY_TRADE_TYPE_MICROPAY = 'MICROPAY';//刷卡支付

    const WX_PAY_SUCCESS = 'SUCCESS';
    const WX_PAY_FAIL = 'FAIL';

    const SCOPES_SNSAPI_BASE = 'snsapi_base';
    const SCOPES_SNSAPI_USERINFO = 'snsapi_userinfo';


    /**
     * EasyWeChatClient constructor.
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
        $group = self::WX_SDK_TYPE[$type];

        $config = D('Config')->queryConfigByGroup('WECHAT', $group);
        $this->appsecret = $config['appsecret'];
        $this->appid = $config['appid'];
        $this->mchid = $config['mchid'];
        $this->key = $config['key'];
        $this->sslcert_path = SVN_ROOT . $config['sslcert_path'];
        $this->sslkey_path = SVN_ROOT . $config['sslkey_path'];
        $this->notify_url = 'http://' . C('PAY_MODULE') . $config['notify_url'];
        $this->options = [
            'app_id' => $this->appid,

        ];
        if ($type == self::WX_SDK_TYPE_WAP || $type == self::WX_SDK_TYPE_YM9 || $type == self::WX_SDK_TYPE_QIAN_WAP) {
            $this->options['secret'] = $this->appsecret;
        }
        $this->getApplication();
    }

    /**
     *
     * @return Application
     */
    private function getApplication()
    {
        $this->app = new Application($this->options);
        return $this->app;
    }

    /**
     *
     * @return Payment
     */
    public function getPayment()
    {
        $merchant = new Merchant([
            'app_id' => $this->appid,
            'merchant_id' => $this->mchid,
            'key' => $this->key,
            'cert_path' => $this->sslcert_path,
            'key_path' => $this->sslkey_path,
            'notify_url' => $this->notify_url
        ]);
        return new Payment($merchant);
    }


    /**
     * 统一下单
     * @param $type
     * @param $member_id
     * @param $payment_sn
     * @param $total_fee
     * @return array|string
     */
    public function prepare($type, $member_id, $payment_sn, $total_fee)
    {
        $attributes = [
            'trade_type' => $type, // JSAPI，NATIVE，APP...
            'body' => '酒商酒汇-' . $payment_sn,
            'out_trade_no' => generateSequenceNo(Constant::ORDER_WX_PAY_NO),
            'total_fee' => $total_fee * 100,
        ];
        if ($type == self::WX_PAY_TRADE_TYPE_JS_API) {
            $attributes['openid'] = $this->getOpenId();
        }
        $order = new Order($attributes);
        $result = $this->getPayment()->prepare($order);
        $config = '';
        if ($result->return_code == self::WX_PAY_SUCCESS && $result->result_code == self::WX_PAY_SUCCESS) {
            switch ($type) {
                case 'APP':
                    $config = $this->getPayment()->configForAppPayment($result->prepay_id);
                    break;
                case 'JSAPI':
                    $config = $this->getPayment()->configForPayment($result->prepay_id);
                    break;
            }
        }
        if ($config) {
            $log = array(
                'member_id' => $member_id,
                'payment_sn' => $payment_sn,
                'method' => '统一下单',
                'out_trade_no' => $attributes['out_trade_no'],
                'total_fee' => $attributes['total_fee'],
                'trade_type' => $attributes['trade_type'],
                'spbill_create_ip' => get_client_ip(),
                'time_start' => date("Y-m-d H:i:s"),
                'notify_url' => $this->notify_url,
                'prepay_id' => $result->prepay_id
            );
            $log['req_code'] = $result->return_code;
            $log['req_msg'] = $result->return_msg;
            D('WxpayOrder')->add($log);
        }
        return $config;
    }

    //修改回调地址
    public function setNotifyUrl($notify_url)
    {
        $this->notify_url = $notify_url;
    }

    /**
     * 查询单条订单
     * @param $out_trade_no
     * @return \EasyWeChat\Support\Collection
     */
    public function queryOrderInfo($out_trade_no)
    {
        $orderInfo = $this->getPayment()->query($out_trade_no);
        return $orderInfo;
    }

    /**
     * 关闭微信订单
     * 注意：订单生成后不能马上调用关单接口，最短调用时间间隔为5分钟。
     * @param $out_trade_no
     * @return \EasyWeChat\Support\Collection
     */
    public function closeOrder($out_trade_no)
    {
        $ret = $this->getPayment()->close($out_trade_no);
        return $ret;
    }

    /**
     * OAuth验证
     * @param $scopes
     * @param $callback
     */
    public function weChatOAuth($scopes, $callback)
    {
        $this->options['oauth'] = [
            'scopes' => ['snsapi_base'],
            'callback' => $callback
        ];
        $oauth = $this->getApplication()->oauth;
        $response = $oauth->scopes([$scopes])->redirect();
        $response->send();
    }

    /**
     * 获取openId
     * @return mixed
     */
    public function getOpenId()
    {
        return $this->getUser()->getId();
    }

    /**
     * 从回调中获取用户信息
     * @return mixed
     */
    public function getUser()
    {
        return $this->app->oauth->user();
    }

    /**
     * @param mixed $oauthCallback
     */
    public function setOauthCallback($oauthCallback)
    {
        $this->oauthCallback = $oauthCallback;
    }

    /**
     * @info 获取红包发送接口
     * @return mixed
     * @param $mch_billno
     * @param $send_name        发送名称
     * @param $open_id
     * @param $total_amount     红包金额
     * @param $wishing          祝福语
     * @param $act_name         活动名
     * @param $client_ip        IP
     * @param $remark           备注
     * @return mixed
     */
    public function sendLuckyMoney($mch_billno, $send_name, $open_id, $total_amount, $wishing, $act_name, $client_ip, $remark)
    {
        $this->options['payment'] = array(
            'merchant_id' => $this->mchid,
            'key' => $this->key,
            'cert_path' => $this->sslcert_path,
            'key_path' => $this->sslkey_path,
        );
        $this->getApplication();
        $luckyMoneyData = [
            'mch_billno' => $mch_billno,//mch_id+yyyymmdd+10位
            'send_name' => $send_name,
            're_openid' => $open_id,
            'total_num' => 1,  //固定为1，可不传
            'total_amount' => $total_amount,  //单位为分，不小于100
            'wishing' => $wishing,
            'client_ip' => $client_ip,
            'act_name' => $act_name,
            'remark' => $remark,
        ];
        //如果红包金额大于200则要输入使用场景
        if ($total_amount > 20000) {
            $luckyMoneyData['scene_id'] = "PRODUCT_1";
        }
        return $this->app->lucky_money->send($luckyMoneyData, API::TYPE_NORMAL);
    }

    /**
     * @info 获取mchid
     * @return mixed
     */
    public function getMchid()
    {
        return $this->mchid;
    }
}