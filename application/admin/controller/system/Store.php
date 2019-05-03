<?php 
// 用户
namespace app\admin\controller\system;
use app\admin\controller\ApiCommon;

class Store extends ApiCommon {
    public function add() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $model = model('SystemStore');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('SystemStore');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('SystemStore');
        $data = $model->updateStore($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('SystemStore');
        $params =  input('post.');
        $data = $model->deleteStore($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $model = model('Ad');
        $params =  input('post.');
        $data = $model->getAdInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}