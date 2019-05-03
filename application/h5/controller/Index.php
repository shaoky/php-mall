<?php 
// 用户
namespace app\h5\controller;
use think\Controller;
class Index extends Controller {
    public function getData() {
        // $params =  input('post.');
        // dump(phpinfo());
        $model = model('Index');
        $data = $model->getIndexData();
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function goods() {
        $params =  input('post.');
        $model = model('Index');
        $data = $model->getGoodsList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function store() {
        $params =  input('post.');
        $model = model('Index');
        $data = $model->getStoreInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function share() {
        $params =  input('post.');
        $model = model('Index');
        $data = $model->getShareInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}