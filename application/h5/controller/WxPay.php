<?php 
// 用户
namespace app\h5\controller;
use think\Controller;
use EasyWeChat\Factory;
// use Naixiaoxin\ThinkWechat\Facade;

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

    public function wxPay() {
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
        $config = config('wechat.payment.h5');
        $app = Factory::payment($config);
        $response = $app->handlePaidNotify(function($message, $fail){
            $model = model('wxPay');
            $data = $model->paysuccess($message);
            return true;
        });
        $response->send();
    }

    // public function shopWxPay() {
    //     $params =  input('post.');
    //     $validate = $this->validate($params, 'app\h5\validate\Order.settlement');
    //     if ($validate !== true) {
    //         return resultArray(['error' => $validate]);
    //     }
    //     $model = model('wxPay');
    //     $data = $model->shopSettlement($params);
    //     if (!$data) {
    //         return resultArray(['error' => $model->getError()]);
    //     }
    //     return resultArray(['data' => $data]);
    // }

    // public function shopPaysuccess () {
    //     $config = config('wechat.payment.h5');
    //     $app = Factory::payment($config);
    //     $response = $app->handlePaidNotify(function($message, $fail){
    //         $model = model('wxPay');
    //         $data = $model->shopPaysuccess($message);
    //         return true;
    //     });
    //     $response->send();
    // }

    public function selectOrder () {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.selectOrder');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $app = Facade::payment();
        $data = $app->order->queryByOutTradeNumber($params['orderNo']);
        dump($data);
        if ($data['return_code'] == 'SUCCESS') {
            if ($data['err_code'] == 'ORDERNOTEXIST') {
                return resultArray(['error' => '订单号不存在']);
            }
        }
        
    }
}