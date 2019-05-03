<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/26
 * Time: 9:16
 */

namespace app\app\controller;


use think\Controller;
use app\app\model\Shop as ShopModels;
use think\Request;

class Shop extends ApiCommon
{
    /**
     * 店铺中心
     */
    public function getShopInfo(){
        $ShopModel = new ShopModels();
//        $ShopModel = model('shop');
        $data = $ShopModel->getShopInfo();

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 获取收款码
     */
    public function getGatheringQcode(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->getShopQcode($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺描述
     */
    public function getShopSubtitle(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->getShopSubtitle($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 编辑店铺描述
     */
    public function updateShopSubtitle(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->updateShopSubtitle($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺照片
     */
    public function getShopImage(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->getShopImage($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺照片新增
     */
    public function addShopImage(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->addShopImage($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺照片删除
     */
    public function delShopImage(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->delShopImage($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺营业状态
     */
    public function getShopStatus(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->getShopStatus($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 店铺营业状态编辑
     */
    public function updateShopStatus(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->updateShopStatus($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
    /**
     * 提现申请
     */
    public function applyWith(){
        $ShopModel = new ShopModels();
        $params =  input('post.');
        $data = $ShopModel->applyWith($params);

        if (!$data) {
            return resultArray(['error' => $ShopModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
}