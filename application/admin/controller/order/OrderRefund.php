<?php
// 用户
namespace app\admin\controller\order;
use think\Controller;
use think\Request;
class OrderRefund extends Controller {

    public function list(Request $request)
    {
        $OrderRefundModel = model('OrderRefund');

        $data = $OrderRefundModel->getOrderRefundList($request);


        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function update()
    {
        $params =  input('post.');
        $OrderRefundModel = model('OrderRefund');

        $data = $OrderRefundModel->OrderRefundUpdate($params);

        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function info(Request $request)
    {

        $OrderRefundModel = model('OrderRefund');

        $data = $OrderRefundModel->OrderRefundInfo($request);

        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);

    }

    public function refund() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Order.refund');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('OrderRefund');
        $data = $model->setRefund($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}
