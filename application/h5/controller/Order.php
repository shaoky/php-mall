<?php 
// 用户
namespace app\h5\controller;
use app\h5\controller\ApiCommon;
use think\Controller;
class Order extends ApiCommon {
    public function cartValidate() {
        $model = model('Order');
        $data = $model->cartValidate();
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function goodsPreview() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.goodsPreview');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->goodsPreview($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function cartPreview() {
        $params =  input('post.');
        $model = model('Order');
        $data = $model->cartPreview($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.add');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function delete() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.delete');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->deleteOrder($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function list() {
        $params =  input('post.');
        $model = model('Order');
        $data = $model->orderList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function cancelOrder() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.cancel');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->cancelOrder($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function confirmOrder() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.confirm');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->confirmOrder($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function info() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Order.info');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->getOrderInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}