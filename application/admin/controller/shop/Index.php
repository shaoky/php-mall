<?php 
// 用户
namespace app\admin\controller\shop;
use app\admin\controller\ApiCommon;

class Index extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Shop');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('Shop');
        $data = $adModel->addData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('商铺添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $adModel = model('Shop');
        $params =  input('post.');
        $data = $adModel->getList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('商铺列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Shop');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('Shop');
        $data = $adModel->updateData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('商铺修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function updateDiscount() {
        $params =  input('post.');
        $adModel = model('Shop');
        $data = $adModel->updateDiscount($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('商铺修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function info() {
        $adModel = model('Shop');
        $params =  input('post.');
        $data = $adModel->getInfo($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('商铺详情', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function setIsOpen() {
        $adModel = model('Shop');
        $params =  input('post.');
        $data = $adModel->setIsOpen($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        if ($params['isOpen'] == 0) {
            $this->adminLog('店铺显示', $this->nowTime);
        } else {
            $this->adminLog('店铺不显示', $this->nowTime);
        }
        return resultArray(['data' => $data]);
    }

    public function setAuditStatus() {
        $adModel = model('Shop');
        $params =  input('post.');
        $data = $adModel->setAuditStatus($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('店铺审核', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function makeQr(){
        $adModel = model('Shop');
        $params =  input('post.');
        $data = $adModel->makeQrcode($params['shopId'],$params['userId']);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
//        $this->adminLog('店铺审核', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}