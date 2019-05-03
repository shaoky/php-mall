<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class AttrRelation extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $model = model('GoodsAttrRelation');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsAttrRelation');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('GoodsAttrRelation');
        $data = $model->updateGoodsType($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsAttrRelation');
        $params =  input('post.');
        $data = $model->deleteGoodsType($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $model = model('GoodsAttrRelation');
        $params =  input('post.');
        $data = $model->getAdInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}