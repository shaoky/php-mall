<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Attr extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsAttr.add');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('GoodsAttr');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsAttr');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsAttr.update');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('GoodsAttr');
        $data = $model->updateGoodsAttr($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsAttr.delete');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('GoodsAttr');
        $data = $model->deleteGoodsAttr($params);
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