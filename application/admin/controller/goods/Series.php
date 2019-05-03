<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Series extends ApiCommon {
    public function add() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $model = model('GoodsSeries');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('GoodsSeries');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $model = model('GoodsSeries');
        $data = $model->updateGoodsSeries($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('GoodsSeries');
        $params =  input('post.');
        $data = $model->deleteGoodsSeries($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}