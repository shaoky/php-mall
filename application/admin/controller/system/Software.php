<?php 
// 用户
namespace app\admin\controller\system;
use app\admin\controller\ApiCommon;

class Software extends ApiCommon {
    public function add() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $model = model('SystemSoftware');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('SystemSoftware');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('SystemSoftware');
        $data = $model->updateSoftware($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('SystemSoftware');
        $params =  input('post.');
        $data = $model->deleteSystemSoftware($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}