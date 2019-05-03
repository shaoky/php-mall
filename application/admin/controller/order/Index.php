<?php 
// 用户
namespace app\admin\controller\order;
use app\admin\controller\ApiCommon;

class Index extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('GoodsType');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('Order');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('GoodsType');
        $data = $model->updateGoodsType($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsType');
        $params =  input('post.');
        $data = $model->deleteGoodsType($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function info() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Order.info');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->getInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单详情', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delivery() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Order.delivery');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->setDelivery($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单发货', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function cancel() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Order.cancel');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Order');
        $data = $model->setCancel($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        $this->adminLog('订单取消', $this->nowTime);
        return resultArray(['data' => $data]);
    }

   
}