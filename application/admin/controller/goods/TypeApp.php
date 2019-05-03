<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class TypeApp extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $model = model('GoodsTypeApp');
        $data = $model->addData($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsTypeApp');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('GoodsTypeApp');
        $data = $model->updateData($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsTypeApp');
        $params =  input('post.');
        $data = $model->deleteData($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $model = model('GoodsTypeApp');
        $params =  input('post.');
        $data = $model->getData($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}