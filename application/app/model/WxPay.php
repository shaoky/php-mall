<?php  
namespace app\app\model;  
use app\h5\model\Common;
use think\Db;
// use Naixiaoxin\ThinkWechat\Facade;
use EasyWeChat\Factory;
use app\h5\model\Order;

// require '../extend/wxpay/JsApiPay.php';
// use weixinpay\WxPayApi as WeixinpayClass;
/**
 * @apiDefine h5PayGroup h5-支付
 */

class WxPay extends Common {
    private $paymentParams = [];

    public function __construct() {
        $headerParams = $this->getHeaderParams();
        if (empty($headerParams['app'])) {
            $this->paymentParams = config('wechat.payment.shangpin');
        } else if ($headerParams['app'] == 1) {
            $this->paymentParams = config('wechat.payment.shangpin');
        } else if ($headerParams['app'] == 2){
            $this->paymentParams = config('wechat.payment.huiming');
        }
    }
    /**
     * @api {post} /h5/order/pay 1. 微信支付
     * @apiName wxPay
     * @apiGroup h5PayGroup
     * @apiVersion 1.0.0
     */
    public function settlement($params) {
        output_log_file('微信支付开始-----------');
        $user = $this->getUserInfo();
        try {
            $orderType = substr($params['orderNo'], 0 ,2);
            $order = [];
            if ($orderType == '10') {
                $order = Db::name('order')->where('orderNo', $params['orderNo'])->find();
                if ($order == null || $order['payMoney']) {
                    $this->error = '该订单不存在';
                    return;
                }
            }
            if ($orderType == '30') {
                $order = Db::name('shop_order')->where('orderNo', $params['orderNo'])->find();
                if ($order == null || $order['payMoney']) {
                    $this->error = '该订单不存在';
                    return;
                }
            }
            

            // 添加到支付日志表
            $pay = Db::name('pay')->where('orderNo', $order['orderNo'])->find();
            if (!$pay) {
                $map = [
                    'orderNo' => $order['orderNo'],
                    'payMoney' => $order['payableMoney'],
                    'payType' => 1,
                    'payStatus' => 1,
                    'thirdOrderNo' => '',
                    'thirdNo' => 2,
                    'updateTime' => '',
                    'userId' => $user['userId'],
                    'createTime' => time()
                ];
                Db::name('pay')->insert($map);
            }
            

            // $config = [
            //     'app_id' => $this->app_id,
            //     'mch_id' => $this->mch_id,
            //     'key' => $this->key,
            // ];
            // if ($this->headerParamsApp == 2) {
            //     $config['cert_path'] = $this->cert_path;
            //     $config['key_path'] = $this->key_path;
            // }
            output_log_file('1、payment参数:'.json_encode($this->paymentParams));
            $payment = Factory::payment($this->paymentParams); // 微信支付
            // ios和微信
            $unifyParams = [
                'body' => '订单号:'.$params['orderNo'],
                'out_trade_no' => $params['orderNo'],
                'total_fee' => $order['payableMoney'] * 100,
                'trade_type' => 'APP',
                'sign_type' => 'MD5',
                // 'notify_url' => $this->notify_url, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            ];
            output_log_file('1、unify参数:'.json_encode($unifyParams));
            $result = $payment->order->unify($unifyParams);
            output_log_file('1、unify返回:'.json_encode($result));
            
            if ($result['return_code'] == 'FAIL') {
                output_log_file('1、统一下单出错了:'. json_encode($result));
                $this->error = $result['return_msg'].'请联系app管理员';
                return;
            }
            if ($result['result_code'] == 'FAIL') {
                output_log_file('1、统一下单出错了:'. json_encode($result));
                $this->error = $result['err_code_des'].'请联系app管理员';
                return;
            }
            
            // $result['timeStamp'] = time();
            $jssdk = $payment->jssdk;
            $json = $jssdk->appConfig($result['prepay_id']);
            output_log_file('1、返回给app参数:'.json_encode($json));
            return [
                'info' => $json
            ];
           
            
        } catch (\Exception $e) {
            trace('微信订单支付：'.$e->getMessage().'订单号：'.$params['orderNo'], 'error');
            Db::name('log_pay_error')->insert([
                'orderNo' => $params['orderNo'],
                'type' => 4,
                'error' => $e->getMessage(),
                'createTime' => time()
            ]);
            $this->error = $e->getMessage();
            return false;
        }
    }
}