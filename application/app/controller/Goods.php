<?php
// 用户
namespace app\app\controller;
use think\Controller;
class Goods extends Controller {
    public function list() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getGoodsList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getGoodsInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function memberList() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getMemberGoodsList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function goodsShare() {
        $params =  input('post.');
        $model = model('Goods');
        $data = $model->getGoodsInfoShare($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function seriesList() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getGoodsSeriesList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function goodsSearchList() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getGoodsSearchList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function goodsLikeList() {
        $params =  input('post.');
        $model = model('app\h5\model\Goods');
        $data = $model->getGoodsLikeList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}
