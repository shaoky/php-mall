<?php
// 用户
namespace app\admin\controller\statistics;
use app\admin\controller\ApiCommon;
use think\Request;

class Index extends ApiCommon {
    public function goodsList(Request $request)
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getgoodsList($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }
        $this->adminLog('商品销售排行', $this->nowTime);
        return resultArray(['data' => $data]);
    }


    public function ordersList(Request $request)
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getordersList($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }
        $this->adminLog('平台流水', $this->nowTime);
        return resultArray(['data' => $data]);

    }

    public function ordersListExcel(Request $request)
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getOrdersListExcel($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }
        $this->adminLog('平台流水', $this->nowTime);
        return resultArray(['data' => $data]);

    }

    public function index(Request $request)
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->index($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    public function relationMap()
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getRelationMap();

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    public function TransactionProfile(Request $request){
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->TransactionProfile($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }
        $this->adminLog('交易概况', $this->nowTime);
        return resultArray(['data' => $data]);
    }
    public function ComprehensiveOverview(Request $request){
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->ComprehensiveOverview($request);

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }
        $this->adminLog('综合概况', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}
