<?php
// 用户
namespace app\app\controller;
use app\app\controller\ApiCommon;

class UserAddress extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\UserAddress');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $AdPositionModel = model('app\h5\model\UserAddress');
        $data = $AdPositionModel->add($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function list() {
        $params =  input('post.');
        $model = model('app\h5\model\UserAddress');
        $data = $model->list($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\h5\validate\UserAddress');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('app\h5\model\UserAddress');
        $data = $model->updateAddress($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('app\h5\model\UserAddress');
        $params =  input('post.');
        $data = $model->deleteAddress($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function setDefault() {
        $model = model('app\h5\model\UserAddress');
        $params =  input('post.');
        $data = $model->setDefault($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function getDefault() {
        $model = model('app\h5\model\UserAddress');
        $params =  input('post.');
        $data = $model->getDefault($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}
