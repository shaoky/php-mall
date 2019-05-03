<?php
// 用户
namespace app\app\controller;
use think\Controller;
use think\Request;
class OrderRefund extends Controller {

    public function list(Request $request)
    {
        $OrderRefundModel = model('app\h5\model\OrderRefund');

        $data = $OrderRefundModel->getOrderRefundList($request);

        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function add(Request $request)
    {

        $validate = $this->validate($request->param(), 'app\h5\validate\OrderRefund');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }

        $OrderRefundModel = model('app\h5\model\OrderRefund');

        $data = $OrderRefundModel->OrderRefundAdd($request);

        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function info(Request $request)
    {

        $OrderRefundModel = model('app\h5\model\OrderRefund');

        $data = $OrderRefundModel->OrderRefundInfo($request);

        if (!$data) {
            return resultArray(['error' => $OrderRefundModel->getError()]);
        }

        return resultArray(['data' => $data]);

    }

}
