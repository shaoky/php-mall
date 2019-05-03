<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Seckill extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\GoodsSeckill');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $adModel = model('GoodsSeckill');
        $data = $adModel->addData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('抢购活动增加', $this->nowTime);
        return resultArray(['data' => $data]);
    }
  
    public function list() {
        $adModel = model('GoodsSeckill');
        $params =  input('post.');
        $data = $adModel->getDataList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('抢购活动列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $params =  input('post.');
        // $validate = $this->validate($params, 'app\admin\validate\Ad');
        // if ($validate !== true) {
        //     return resultArray(['error' => $validate]);
        // }
        $adModel = model('GoodsSeckill');
        $data = $adModel->updateData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('抢购活动修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $adModel = model('GoodsSeckill');
        $params =  input('post.');
        $data = $adModel->deleteData($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        $this->adminLog('抢购活动删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }

}