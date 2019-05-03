<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Activity extends ApiCommon {
    public function add() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\Ad');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $adModel = model('GoodsActivity');
        $data = $adModel->addAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('活动商品添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $adModel = model('GoodsActivity');
        $params =  input('post.');
        $data = $adModel->getAdList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('活动商品列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Ad');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('GoodsActivity');
        $data = $adModel->updateAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('活动商品修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $adModel = model('GoodsActivity');
        $params =  input('post.');
        $data = $adModel->deleteAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('活动商品删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function info() {
        $adModel = model('GoodsActivity');
        $params =  input('post.');
        $data = $adModel->getAdInfo($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('活动商品详情', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}