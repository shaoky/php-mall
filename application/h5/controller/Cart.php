<?php
// 用户
namespace app\h5\controller;
use app\h5\controller\ApiCommon;
use think\Request;

class Cart extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Cart.add');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $AdPositionModel = model('Cart');
        $data = $AdPositionModel->add($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function list() {
        $params =  input('post.');
        $model = model('Cart');
        $data = $model->list($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\Cart.update');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('Cart');
        $data = $model->updateCart($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('Cart');
        $params =  input('post.');
        $data = $model->deleteCart($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function select(Request $request) {
        $model = model('Cart');
        $params =  input('post.');
        $data = $model->select($request);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}
