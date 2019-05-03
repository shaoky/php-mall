<?php 
// 用户
namespace app\admin\controller\ad;
use app\admin\controller\ApiCommon;

class Position extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\AdPosition');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $AdPositionModel = model('AdPosition');
        $data = $AdPositionModel->add($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        $this->adminLog('广告位置添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function list() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\AdPosition');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $AdPositionModel = model('AdPosition');
        $data = $AdPositionModel->list($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        $this->adminLog('广告位置列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\AdPosition');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $AdPositionModel = model('AdPosition');
        $data = $AdPositionModel->updateAd($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        $this->adminLog('广告位置更新', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $AdPositionModel = model('AdPosition');
        $params =  input('post.');
        $data = $AdPositionModel->deleteAd($params);
        if (!$data) {
            return resultArray(['error' => $AdPositionModel->getError()]);
        }
        $this->adminLog('广告位置删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }
}