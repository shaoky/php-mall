<?php 
// 会员等级规则
namespace app\admin\controller\user;
use app\admin\controller\ApiCommon;

class Level extends ApiCommon {
    public function list() {
        $params =  input('post.');
        $model = model('UserLevel');
        $data = $model->list($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function add() {
        $params =  input('post.');
        $model = model('UserLevel');
        $data = $model->add($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function info() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\UserLevel.delete');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('UserLevel');
        $data = $model->getInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function delete() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\UserLevel.delete');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('UserLevel');
        $data = $model->deleteLevel($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}