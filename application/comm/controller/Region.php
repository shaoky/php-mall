<?php 
// 用户
namespace app\comm\controller;
use think\Controller;

class Region extends Controller {
    public function all() {
        $params =  input('post.');
        $model = model('Region');
        $data = $model->getRegionAll($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function city() {
        $model = model('Region');
        $data = $model->getCityAll();
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}