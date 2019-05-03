<?php
// 用户
namespace app\app\controller;
use app\app\controller\ApiCommon;
use think\Request;
class Buyer extends ApiCommon {
    public function index()
    {

        $BuyerModel = model('Buyer');
        $data = $BuyerModel->getIndexData();

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function invitation()
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->invitation();

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function share()
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->share();

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function myClient()
    {
        $StatisticsModel = model('Statistics');

        $data = $StatisticsModel->getMyClient();

        if (!$data) {
            return resultArray(['error' => $StatisticsModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function getContact()
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->getContactData();

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function updateContact(Request $request)
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->updateContact($request);

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }


    public function getCash()
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->getCashData();

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function getCashList(Request $request)
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->getCashList($request);

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function getCashInfo(Request $request)
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->getCashInfo($request);

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function CashAdd(Request $request)
    {
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->AddCash($request);

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function bankInfo()
    {
        $params =  input('post.');
        $BuyerModel = model('Buyer');
        $data = $BuyerModel->bankInfo($params);

        if (!$data) {
            return resultArray(['error' => $BuyerModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }


}
