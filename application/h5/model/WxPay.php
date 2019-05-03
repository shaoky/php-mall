<?php  
namespace app\h5\model;  
use app\h5\model\Common;
use think\Db;
use Naixiaoxin\ThinkWechat\Facade;
use EasyWeChat\Factory;
use app\h5\model\Order;
use app\apphm\model\ShopOrder;


// require '../extend/wxpay/JsApiPay.php';
// use weixinpay\WxPayApi as WeixinpayClass;
/**
 * @apiDefine h5PayGroup h5-支付
 */

class WxPay extends Common {
    /**
     * @api {post} /h5/order/pay 1. 微信支付
     * @apiName wxPay
     * @apiGroup h5PayGroup
     * @apiVersion 1.0.0
     */
    public function settlement($params) {
        $user = $this->getUserInfo();
        try {
            $order = Db::name('order')->where('orderNo', $params['orderNo'])->find();

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
            $paymentConfig = config('wechat.payment.h5');
            $payment = Factory::payment($paymentConfig);
            
            // h5支付
            $result = $payment->order->unify([
                'body' => '订单号:'.$params['orderNo'],
                'out_trade_no' => $params['orderNo'],
                'total_fee' => $order['payableMoney'] * 100,
                // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                // 'notify_url' => config('app.wxpay_h5_notify_url'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI',
                'openid' => $user['openid'],
            ]);
            if (!empty($result['err_code'])) {
                if (!empty($result['err_code']) == 'ORDERPAID') {
                    \think\facade\Log::record( '订单号：'.$order['orderNo']. json_encode($result),'error');
                    $this->error = $result['err_code_des'];
                    return;
                }
            }
            if (!empty($result['return_code'])) {
                if ($result['return_code']== 'FAIL') {
                    \think\facade\Log::record( '订单号：'.$order['orderNo']. json_encode($result),'error');
                    $this->error = $result['return_msg'];
                    return;
                }
            }
            $jssdk = $payment->jssdk;
            $json = $jssdk->bridgeConfig($result['prepay_id']);
            $data['info'] = json_decode($json);
            return $data;

            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * 支付成功，微信回调的接口
     */
    public function paysuccess($params) {
        output_log_file('微信支付回调参数：'.json_encode($params));
        $orderType = substr($params['out_trade_no'], 0 ,2);
        $order = [];
        if ($orderType == '10') {
            $order = Db::name('order')->where('orderNo', $params['out_trade_no'])->find();
            if ($order == null || $order['payMoney']) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
        }

        if ($orderType == '30') {
            $order = Db::name('shop_order')->where('orderNo', $params['out_trade_no'])->find();
            if ($order == null || $order['payMoney']) { // 如果订单不存在 或者 订单已经支付过了
                return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
            }
        }
        

        // 增加到日志表
        try {
            $map = [
                'appid' => $params['appid'],
                'mch_id' => empty($params['mch_id']) ? '' : $params['mch_id'],
                'device_info' => empty($params['device_info']) ? '' : $params['device_info'],
                'nonce_str' => $params['nonce_str'],
                'sign' => $params['sign'],
                'result_code' => $params['result_code'],
                'err_code' => empty($params['err_code']) ? '' : $params['err_code'],
                'err_code_des' => empty($params['err_code_des']) ? '' : $params['err_code_des'],
                'openid' => empty($params['openid']) ? '' : $params['openid'],
                'is_subscribe' => empty($params['is_subscribe']) ? '' : $params['is_subscribe'],
                'trade_type' => $params['trade_type'],
                'bank_type' => $params['bank_type'],
                'total_fee' => $params['total_fee'],
                'fee_type' => $params['fee_type'],
                'cash_fee' => $params['cash_fee'],
                'cash_fee_type' => empty($params['cash_fee_type']) ? '' : $params['cash_fee_type'],
                'coupon_fee' => empty($params['coupon_fee']) ? '' : $params['coupon_fee'],
                'coupon_count' => empty($params['coupon_count']) ? '' : $params['coupon_count'],
                'transaction_id' => empty($params['transaction_id']) ? '' : $params['transaction_id'],
                'out_trade_no' => empty($params['out_trade_no']) ? '' : $params['out_trade_no'],
                'attach' => empty($params['attach']) ? '' : $params['attach'],
                'time_end' => empty($params['time_end']) ? '' : $params['time_end'],
                'content' => json_encode($params)
            ];
            Db::name('log_wxpay')->insert($map);
        } catch (\Exception $e) {
            // $this->error = $e->getMessage();
            Db::name('log_pay_error')->insert(['type' => 1, 'orderNo' => $order['orderNo'], 'error' => $e->getMessage(), 'createTime' => time()]);
        }
        

        if ($params['return_code'] == 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($params['result_code'] == 'SUCCESS') {
                
                $form = [
                    'orderNo' => $params['out_trade_no'],
                    'payMoney' => $map['total_fee'] / 100,
                    'payType' => 2
                ];
                if ($orderType == '10'){
                    $payOrder = Order::payOrder($form);
                }
                if ($orderType == '30'){
                    $payOrder = ShopOrder::payOrder($form);
                }

               
                
                if ($payOrder == 1) {
                    // Db::name('log_wxpay')->insert(['content' => $payOrder]);
                    // 成功之后改不了状态，待处理
                    try {
                        Db::name('pay')->where('orderNo', $params['out_trade_no'])->update([
                            'thirdOrderNo' => $params['transaction_id'],
                            'payStatus' => 3,
                            'updateTime' => time()
                        ]);
                        // Db::name('order')->where('orderNo', $params['out_trade_no'])->update([
                        //     'payMoney' => $params['total_fee'],
                        // ]);
                    } catch (\Exception $e) {
                        // $this->error = $e->getMessage();
                        Db::name('log_pay_error')->insert(['type' => 3, 'orderNo' => $order['orderNo'], 'error' => $e->getMessage(), 'createTime' => time()]);
                    }
                    return true;
                }
    
            // 用户支付失败
            } elseif ($params['result_code'] == 'FAIL') {
                Db::name('pay')->where('orderNo', $params['out_trade_no'])->update([
                    'thirdOrderNo' => $params['out_trade_no'],
                    'payStatus' => 2,
                    'updateTime' => time()
                ]);
                // $order->status = 'paid_fail';
            }
        } else {
            return $fail('通信失败，请稍后再通知我');
        }
        return true;
        // $this->output_log_file(json_encode($params));
    }

    /**
     * @api {post} /h5/shop/order/pay 3. 商户微信支付
     * @apiName shopSettlement
     * @apiGroup h5PayGroup
     * @apiVersion 1.0.0
     */
    public function shopSettlement($params) {
        $user = $this->getUserInfo();
        try {
            $order = Db::name('shop_order')->where('orderNo', $params['orderNo'])->find();
            // 补个已支付
            if ($order == null || $order['payMoney']) {
                $this->error = '该订单不存在';
                return;
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
            $paymentConfig = config('wechat.payment.h5');
            $payment = Factory::payment($paymentConfig);
            
            // h5支付
            $result = $payment->order->unify([
                'body' => '订单号:'.$params['orderNo'],
                'out_trade_no' => $params['orderNo'],
                'total_fee' => $order['payableMoney'] * 100,
                // 'spbill_create_ip' => '123.12.12.123', // 可选，如不传该参数，SDK 将会自动获取相应 IP 地址
                // 'notify_url' => config('app.wxpay_h5_notify_url'), // 支付结果通知网址，如果不设置则会使用配置里的默认地址
                'trade_type' => 'JSAPI',
                'openid' => $user['openid'],
            ]);
            if (!empty($result['err_code'])) {
                if (!empty($result['err_code']) == 'ORDERPAID') {
                    \think\facade\Log::record( '订单号：'.$order['orderNo']. json_encode($result),'error');
                    $this->error = $result['err_code_des'];
                    return;
                }
            }
            if (!empty($result['return_code'])) {
                if ($result['return_code']== 'FAIL') {
                    \think\facade\Log::record( '订单号：'.$order['orderNo']. json_encode($result),'error');
                    $this->error = $result['return_msg'];
                    return;
                }
            }
            $jssdk = $payment->jssdk;
            $json = $jssdk->bridgeConfig($result['prepay_id']);
            $data['info'] = json_decode($json);
            return $data;

            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * 支付成功，微信回调的接口
     */
    public function shopPaysuccess($params) {
        output_log_file('微信支付回调参数：'.json_encode($params));
        $order = Db::name('shop_order')->where('orderNo', $params['out_trade_no'])->find();
        if ($order == null || $order['payMoney']) { // 如果订单不存在 或者 订单已经支付过了
            return true; // 告诉微信，我已经处理完了，订单没找到，别再通知我了
        }

        // 增加到日志表
        try {
            $map = [
                'appid' => $params['appid'],
                'mch_id' => empty($params['mch_id']) ? '' : $params['mch_id'],
                'device_info' => empty($params['device_info']) ? '' : $params['device_info'],
                'nonce_str' => $params['nonce_str'],
                'sign' => $params['sign'],
                'result_code' => $params['result_code'],
                'err_code' => empty($params['err_code']) ? '' : $params['err_code'],
                'err_code_des' => empty($params['err_code_des']) ? '' : $params['err_code_des'],
                'openid' => empty($params['openid']) ? '' : $params['openid'],
                'is_subscribe' => empty($params['is_subscribe']) ? '' : $params['is_subscribe'],
                'trade_type' => $params['trade_type'],
                'bank_type' => $params['bank_type'],
                'total_fee' => $params['total_fee'],
                'fee_type' => $params['fee_type'],
                'cash_fee' => $params['cash_fee'],
                'cash_fee_type' => empty($params['cash_fee_type']) ? '' : $params['cash_fee_type'],
                'coupon_fee' => empty($params['coupon_fee']) ? '' : $params['coupon_fee'],
                'coupon_count' => empty($params['coupon_count']) ? '' : $params['coupon_count'],
                'transaction_id' => empty($params['transaction_id']) ? '' : $params['transaction_id'],
                'out_trade_no' => empty($params['out_trade_no']) ? '' : $params['out_trade_no'],
                'attach' => empty($params['attach']) ? '' : $params['attach'],
                'time_end' => empty($params['time_end']) ? '' : $params['time_end'],
                'content' => json_encode($params)
            ];
            Db::name('log_wxpay')->insert($map);
        } catch (\Exception $e) {
            // $this->error = $e->getMessage();
            Db::name('log_pay_error')->insert(['type' => 1, 'orderNo' => $order['orderNo'], 'error' => $e->getMessage(), 'createTime' => time()]);
        }
        

        if ($params['return_code'] == 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
            // 用户是否支付成功
            if ($params['result_code'] == 'SUCCESS') {
                $form = [
                    'orderNo' => $params['out_trade_no'],
                    'payMoney' => $map['total_fee'] / 100,
                    'payType' => 2
                ];
                $payOrder = SshopOrder::payOrder($form);
                
                if ($payOrder == 1) {
                    // Db::name('log_wxpay')->insert(['content' => $payOrder]);
                    // 成功之后改不了状态，待处理
                    try {
                        Db::name('pay')->where('orderNo', $params['out_trade_no'])->update([
                            'thirdOrderNo' => $params['transaction_id'],
                            'payStatus' => 3,
                            'updateTime' => time()
                        ]);
                        // Db::name('order')->where('orderNo', $params['out_trade_no'])->update([
                        //     'payMoney' => $params['total_fee'],
                        // ]);
                    } catch (\Exception $e) {
                        // $this->error = $e->getMessage();
                        Db::name('log_pay_error')->insert(['type' => 3, 'orderNo' => $order['orderNo'], 'error' => $e->getMessage(), 'createTime' => time()]);
                    }
                    return true;
                }
    
            // 用户支付失败
            } elseif ($params['result_code'] == 'FAIL') {
                Db::name('pay')->where('orderNo', $params['out_trade_no'])->update([
                    'thirdOrderNo' => $params['out_trade_no'],
                    'payStatus' => 2,
                    'updateTime' => time()
                ]);
                // $order->status = 'paid_fail';
            }
        } else {
            return $fail('通信失败，请稍后再通知我');
        }
        return true;
        // $this->output_log_file(json_encode($params));
    }

    public function output_log_file($str,$type="alipay", $id=1)
    {
        $date = date('Y-m-d');
        if (PHP_OS == 'Linux') {
//            $path = DOCROOT . "logs/$type/$date";
////        var_dump($path);
            $path = "/var/log";
            $filename = $path . '/' . "weixin.log";
        } else {
            $path = DOCROOT . "logs\\$type\\$date";
//        var_dump($path);
            $filename = $path . '\\' . "weixin.log";
        }
//        var_dump($path);
//        if (!is_dir($filename)) {
//            mkdir($filename, 0777, true);
//        }
        $files = fopen($filename, 'a');
//        var_dump($filename);
        fwrite($files, "\r\n".$str);
        fclose($files);
    }

    public function pay()
    {
        // 获取jssdk需要用到的数据
        $data = $wxpay->getParameters();
        // 将数据分配到前台页面
        return $this->fetch('', [
           'data'=>json_encode($data)
        ]);
    }

    public static function refund($number, $refundNumber, $totalFee, $refundFee) {
        $app = Facade::payment();
        $app->refund->byOutTradeNumber($number, $refundNumber, $totalFee, $refundFee);
    }

    public static function refundStatus($transactionId) {
        $app = Facade::payment();
        $result = $app->refund->queryByTransactionId($transactionId);
        return $result;
    }
}