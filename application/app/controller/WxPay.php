<?php 
// 用户
namespace app\app\controller;
use think\Controller;
use Naixiaoxin\ThinkWechat\Facade;
use EasyWeChat\Factory;

class WxPay extends Controller {
    public function pay() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.pay');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->payOrder($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function settlement() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.settlement');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('wxPay');
        $data = $model->settlement($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function paysuccess () {
        output_log_file('2、回调接口开始');
        $config = config('wechat.payment.shangpin');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function($message, $fail){
            // output_log_file('回调参数:'.json_encode($message));
            $model = model('app\h5\model\WxPay');
            // output_log_file('A:');
            $data = $model->paysuccess($message);
            // output_log_file('B:');
            return true;
        });
        $response->send();
    }

    // 
    public function huimingPaysuccess () {
        output_log_file('2、回调接口开始');
        $config = config('wechat.payment.huiming');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function($message, $fail){
            // output_log_file('回调参数:'.json_encode($message));
            $model = model('app\h5\model\WxPay');
            // output_log_file('A:');
            $data = $model->paysuccess($message);
            // output_log_file('B:');
            return true;
        });
        $response->send();
    }

    public function selectOrder () {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.selectOrder');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $app = Facade::payment();
        $data = $app->order->queryByOutTradeNumber($params['orderNo']);
        // dump($data);
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['err_code'] == 'ORDERNOTEXIST') {
                return resultArray(['error' => '订单号不存在']);
            }
        }
        
    }
}