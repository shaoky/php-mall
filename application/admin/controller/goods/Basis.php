<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Basis extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $model = model('GoodsBasis');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsBasis');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('GoodsBasis');
        $data = $model->updateGoodsBasis($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsBasis');
        $params =  input('post.');
        $data = $model->deleteGoodsBasis($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $model = model('GoodsBasis');
        $params =  input('post.');
        $data = $model->getGoodsBasisInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}