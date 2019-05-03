<?php
// 用户
namespace app\app\controller;
use app\app\controller\ApiCommon;
use think\Request;
class Statistics extends ApiCommon {
    public function dataList(Request $request)
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getDataList($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function myClient()
    {
        $params =  input('post.');
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getMyClient($params);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function orderList(Request $request)
    {

        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getOrderList($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

}
