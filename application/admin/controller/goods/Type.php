<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Type extends ApiCommon {
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
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsType');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
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
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsType');
        $params =  input('post.');
        $data = $model->deleteGoodsType($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $model = model('GoodsType');
        $params =  input('post.');
        $data = $model->getGoodsTypeInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}