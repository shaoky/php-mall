<?php 
// 用户
namespace app\admin\controller\system;
use app\admin\controller\ApiCommon;

class Version extends ApiCommon {
    public function add() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\GoodsType');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $model = model('SystemVersion');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $model = model('SystemVersion');
        $params =  input('post.');
        $data = $model->getList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $model = model('SystemVersion');
        $data = $model->updateVersion($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $model = model('SystemVersion');
        $params =  input('post.');
        $data = $model->deleteSystemVersion($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

}