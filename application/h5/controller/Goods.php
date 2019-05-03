<?php 
// 用户
namespace app\h5\controller;
use think\Controller;
class Goods extends Controller {
    public function list() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    
    public function info() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function memberList() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getMemberGoodsList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function seriesList() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsSeriesList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function goodsTypeList() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsTypeList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function goodsSearchList() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsSearchList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}