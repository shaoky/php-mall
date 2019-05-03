<?php 
// 用户
namespace app\admin\controller\ad;
use app\admin\controller\ApiCommon;

class Index extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Ad');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('Ad');
        $data = $adModel->addAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('广告添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $adModel = model('Ad');
        $params =  input('post.');
        $data = $adModel->getAdList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('广告列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Ad');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('Ad');
        $data = $adModel->updateAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('广告修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $adModel = model('Ad');
        $params =  input('post.');
        $data = $adModel->deleteAd($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('广告删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function info() {
        $adModel = model('Ad');
        $params =  input('post.');
        $data = $adModel->getAdInfo($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('广告详情', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}