<?php 
// 用户
namespace app\admin\controller\goods;
use app\admin\controller\ApiCommon;

class Index extends ApiCommon {
    public function add() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\Goods');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $goodsModel = model('Goods');
        $data = $goodsModel->addGoods($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        $this->adminLog('商品添加', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function list() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->getList($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        $this->adminLog('商品列表', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function update() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->updateGoods($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        $this->adminLog('商品修改', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function delete() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->deleteGoods($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        $this->adminLog('商品删除', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function setIsOpen() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->setIsOpen($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        if ($params['isOpen'] == 0) {
            $this->adminLog('商品下架', $this->nowTime);
        } else {
            $this->adminLog('商品上架', $this->nowTime);
        }
        return resultArray(['data' => $data]);
    }

    public function info() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->getInfo($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        $this->adminLog('商品详情', $this->nowTime);
        return resultArray(['data' => $data]);
    }

    public function addImage() {
        $goodsModel = model('GoodsImage');
        $params =  input('post.');
        $data = $goodsModel->addImage($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function deleteImage() {
        $goodsModel = model('GoodsImage');
        $params =  input('post.');
        $data = $goodsModel->deleteImage($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }

    public function attrList() {
        $goodsModel = model('Goods');
        $params =  input('post.');
        $data = $goodsModel->getAttrList($params);
        if (!$data) {
            return resultArray(['error' => $goodsModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}