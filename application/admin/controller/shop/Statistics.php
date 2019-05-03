<?php 
// 用户
namespace app\admin\controller\shop;
use app\admin\controller\ApiCommon;

class Statistics extends ApiCommon {
    public function list() {
        $params =  input('post.');
        $adModel = model('ShopStatistics');
        $data = $adModel->getList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('店铺流水列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}