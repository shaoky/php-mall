<?php 
// 用户
namespace app\admin\controller\common;
use think\Controller;
use think\Request;
use app\admin\controller\ApiCommon;

// require 'vendor/autoload.php';

// use think\Request;
// use think\facade\Env;


class Excel extends ApiCommon {
    public function withdrawal (Request $request) {
        $params = input('post.');
        $model = model('userWithdrawal');
        $result = $model->withdrawalExcel($request);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function order () {
        $params = input('post.');
        $model = model('order');
        $result = $model->getOrderExcel($params);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
    public function shop () {
        $params = input('post.');
        $model = model('shop');
        $result = $model->getShopExcel($params);
        if (!$result) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $result]);
    }
}