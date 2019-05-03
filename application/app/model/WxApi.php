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

class WxApi extends Common {
    private $app_id;
    private $mch_id;
    private $key;

    public function __construct() {
        // $this->app_id = 'wx13a781d2f376bcb5';
        // $this->mch_id = '1514970001';
        // $this->key = 'C6vWFNFknoBHBZ7t2fju2ZIkIf5pPPbr';
    }
    
    public function setlectOrder($params) {
        $config = [
            'app_id' => $this->app_id,
            'mch_id' => $this->mch_id,
            'key' =>  $this->key,
        ];
        
        try {
            $app = Facade::payment($config);
            $data = $app->order->queryByOutTradeNumber($params['orderNo']);
            output_log_file('查询订单结果:'.json_encode($data));
            return $data;
        } catch (\Exception $e) {
            output_log_file('app/model/refund/setlectOrder:'.json_encode($e->getMessage()));
            return 2;
        }
    }

    public function refund($number, $refundNumber, $totalFee, $refundFee, $isMemberGoods) {
        if ($isMemberGoods == 1) {
            $paymentParams = config('wechat.payment.shangpin');
        } {
            $paymentParams = config('wechat.payment.huiming');
        }
        $map = [
            'number' => $number,
            'refundNumber' => $refundNumber,
            'totalFee' => $totalFee,
            'refundFee' => $refundFee
        ];
        output_log_file('退款入参:'.json_encode($map));
        // dump($number, $refundNumber, $totalFee, $refundFee);
        // $config = [
        //     'app_id' => 'wx13a781d2f376bcb5',
        //     'mch_id' => '1514970001',
        //     'key' => 'C6vWFNFknoBHBZ7t2fju2ZIkIf5pPPbr',
        //     'cert_path'          => str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']).'/spdscert/apiclient_cert.pem', // XXX: 绝对路径！！！！
        //     'key_path'           => str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']).'/spdscert/apiclient_key.pem',      // XXX: 绝对路径！！！！
        // ];
        try {
            $app = Factory::payment($paymentParams);
            $result = $app->refund->byTransactionId($number, $refundNumber, $totalFee, $refundFee);
            output_log_file('app/model/refund:'.json_encode($result));
            if ($result['return_code'] == 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                 // 用户是否支付成功
                if ($result['result_code'] == 'SUCCESS') {
                    return 1;
                } elseif ($result['result_code'] == 'FAIL') {
                    return '微信返回：'.$result['err_code_des'];
                }
            } else {
                return '通信失败';
            }
            
        } catch (\Exception $e) {
            output_log_file('app/model/refund/catch:'.json_encode($e->getMessage()));
            return 2;
        }
        
    }

    public function refundStatus($transactionId) {
        $config = [
            'app_id' => 'wx13a781d2f376bcb5',
            'mch_id' => '1514970001',
            'key' => 'C6vWFNFknoBHBZ7t2fju2ZIkIf5pPPbr'
        ];
        $app = Factory::payment($config);
        $result = $app->refund->queryByTransactionId($transactionId);
        return $result;
    }
}