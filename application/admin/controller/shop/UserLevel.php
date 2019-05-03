<?php 
// 用户
namespace app\admin\controller\shop;
use app\admin\controller\ApiCommon;

class UserLevel extends ApiCommon {
    public function update() {
        $params =  input('post.');
        $adModel = model('ShopUserLevel');
        $data = $adModel->updateData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('店铺流水列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}