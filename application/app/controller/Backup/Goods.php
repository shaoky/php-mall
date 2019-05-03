<?php 
// 用户
namespace app\app\controller;
use app\app\controller\ApiCommon;
class Goods extends ApiCommon {
    public function list() {
        $goodsModel = model('Goods');
        $data = $goodsModel->getGoodsList();
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $params['goodsId'] = input('post.goodsId/d');
        $goodsModel = model('Goods');
        $data = $goodsModel->getGoodsInfo($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}