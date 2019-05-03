<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/2/26
 * Time: 10:11
 */

namespace app\app\model;


use app\h5\model\common;
use think\Db;
/**
 * @apiDefine appShopGroup app-商铺中心
 */
class Shop extends common
{
    /**
     * @api {post} /app/shop/getshopinfo 1 商铺中心
     * @apiName getShopInfo
     * @apiSuccess {Float} dayTotal 今日收益
     * @apiSuccess {Float} allTotal 总收益
     * @apiSuccess {String} list 消费流水
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */


    public function getShopInfo()
    {
        try {
            $userId = $this->getUserId();
            $where['userId'] = $userId;
            $shopInfo = Db::name('shop')->where($where)->find();
            if(!$shopInfo){
                $data['info'] = '未开通';
            }else{
//                今日收益
                $time = $this->getDayTime();
//                $dayWhere = "createTime > {$time['begintime']} and createTime < {$time['endtime']} and shopId = {$shopInfo['shopId']}";
                $dayWhere = "shopId = {$shopInfo['shopId']}";
                $dayTotal = Db::name('shop_order')->where($dayWhere)->sum('payMoney');
//                总收益
//                $allWhere['userId'] = $userId;
                $allWhere['shopId'] = $shopInfo['shopId'];
                $allTotal = Db::name('shop_order')->where($allWhere)->sum('payMoney');

                $data['dayTotal'] = $dayTotal;
                $data['allTotal'] = $allTotal;
                $data['shopStatus'] = $shopInfo['shopStatus'];
                if($shopInfo['shopStatus'] == 0){
                    $data['shopStatusValue'] = '不营业';
                }
                if($shopInfo['shopStatus'] == 1){
                    $data['shopStatusValue'] = '营业中';
                }
                $data['shopId'] = $shopInfo['shopId'];
//              消费流水
                $whereFlow['shopId'] = $shopInfo['shopId'];
//                var_dump($whereFlow);
                $list = Db::name('shop_order')->where($whereFlow)->select();
                $data['list'] = $list;
            }
//            var_dump($data);die;
//            die;
//            return $shopInfo;
            return $data;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /app/shop/getshopqcode 2 获取收款码
     * @apiName getShopQcode
     * @apiSuccess {Int} shopId 店铺id
     * @apiSuccess {String} shopName 店铺名称
     * @apiSuccess {String} shopGatheringQcode 收款码
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function getShopQcode($params){
        $userId = $this->getUserId();
        $where['shopId'] = $params['shopId'];
        $shopInfo = Db::name('shop')->where($where)->find();
        $data['shopName'] = $shopInfo['shopName'];
        $data['shopGatheringQcode'] = $shopInfo['shopGatheringQcode'];
        return $data;
    }
    /**
     * @api {post} /app/shop/shopsubtitle/get 3 获取店铺描述
     * @apiName getShopSubtitle
     * @apiParam {Int} shopId 店铺id
     * @apiSuccess {String} shopName 店铺名称
     * @apiSuccess {String} shopGatheringQcode 收款码
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function getShopSubtitle($params){
        $userId = $this->getUserId();
        $where['shopId'] = $params['shopId'];
        $shopInfo = Db::name('shop')->where($where)->find();
        $data['shopSubtitle'] = $shopInfo['shopSubtitle'];
        $data['shopId'] = $params['shopId'];
        return $data;
    }
    /**
     * @api {post} /app/shop/shopsubtitle/update 4 编辑店铺描述
     * @apiName updateShopSubtitle
     * @apiParam {String} [shopSubtitle] 店铺描述
     * @apiParam {Int} shopId 店铺id
     * @apiSuccess {String} shopSubtitle 店铺描述
     * @apiSuccess {Int} shopId 店铺id
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function updateShopSubtitle($params){
        $userId = $this->getUserId();
        $where['shopId'] = $params['shopId'];
        $update['shopSubtitle'] = $params['shopSubtitle'];
        try{
            $shopInfo = Db::name('shop')->where($where)->update($update);
            if ($shopInfo) {
                return [
                    'message' => '修改成功',
                    'info' => $params
                ];
            } else {
                $this->error = '修改失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
    /**
     * @api {post} /app/shop/shopimage/get 5 获取店铺图片
     * @apiName getShopImage
     * @apiParam {Int} shopId 店铺id
     * @apiSuccess {String} shopName 店铺名称
     * @apiSuccess {String} shopGatheringQcode 收款码
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function getShopImage($params){
        $userId = $this->getUserId();
        $where['dataId'] = $params['shopId'];
        $where['fromTable'] = 'tp_shop';
        $where['isDelete'] = 0;
        $shopInfo = Db::name('image')->where($where)->select();
        $data['list'] = $shopInfo;
        return $data;
    }
    /**
     * @api {post} /app/shop/shopimage/add 6 新增店铺图片
     * @apiName addShopImage
     * @apiParam {Int} dataId 店铺id
     * @apiParam {String} imageName 图片名称
     * @apiParam {String} imageUrl 图片地址
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function addShopImage($params){
        try{
            $userId = $this->getUserId();
            $add['fromUserType'] = 2;
            $add['imageUrl'] = $params['imageUrl'];
            $add['imageName'] = $params['imageName'];
            $add['createTime'] = time();
            $add['dataId'] = $params['dataId'];
            $add['fromTable'] = 'tp_shop';
            $add['userId'] = $userId;
            $shopInfo = Db::name('image')->insert($add);
            if ($shopInfo) {
                $where['shopId'] = $params['dataId'];
                $list = $this->getShopImage($where);
                return [
                    'message' => '操作成功',
                    'info' => $list
                ];
            } else {
                $this->error = '操作失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
    /**
     * @api {post} /app/shop/shopimage/del 7 删除店铺图片
     * @apiName delShopImage
     * @apiParam {Int} dataId 店铺id
     * @apiParam {Int} imageId 图片id
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function delShopImage($params){
        try{
            $update['isDelete'] = 1;
            $shopInfo = Db::name('image')->where($params)->update($update);
            if ($shopInfo > 0) {
                $where['shopId'] = $params['dataId'];
                $list = $this->getShopImage($where);
                return [
                    'message' => '操作成功',
                    'info' => $list
                ];
            } else {
                $this->error = '操作失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
    /**
     * @api {post} /app/shop/shopstatus/get 8 获取店铺营业状态
     * @apiName getShopStatus
     * @apiParam {Int} shopId 店铺id
     * @apiSuccess {String} serviceStartTime 开始时间
     * @apiSuccess {String} serviceEndTime 结束时间
     * @apiSuccess {String} shopStatus 店铺状态
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function getShopStatus($params){
        $userId = $this->getUserId();
        $where['shopId'] = $params['shopId'];
        $shopInfo = Db::name('shop')->where($where)->find();
        $data['shopStatus'] = $shopInfo['shopStatus'];
        if($shopInfo['shopStatus'] == 0){
            $data['shopStatusValue'] = '不营业';
        }
        if($shopInfo['shopStatus'] == 1){
            $data['shopStatusValue'] = '营业中';
        }
        $data['shopId'] = $params['shopId'];
        $data['serviceStartTime'] = $shopInfo['serviceStartTime'];
        $data['serviceEndTime'] = $shopInfo['serviceEndTime'];
        return $data;
    }
    /**
     * @api {post} /app/shop/shopstatus/update 9 编辑店铺状态
     * @apiName updateShopStatus
     * @apiParam {Int} shopStatus 店铺状态0不开店1开店
     * @apiParam {Int} shopId 店铺id
     * @apiParam {String} serviceStartTime 开始时间
     * @apiParam {String} serviceEndTime 结束时间
     * @apiSuccess {Int} shopId 店铺id
     * @apiSuccess {String} serviceStartTime 开始时间
     * @apiSuccess {String} serviceEndTime 结束时间
     * @apiSuccess {String} shopStatus 店铺状态
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function updateShopStatus($params){
        $userId = $this->getUserId();
        $where['shopId'] = $params['shopId'];
        $update['shopStatus'] = $params['shopStatus'];
        $update['serviceStartTime'] = $params['serviceStartTime'];
        $update['serviceEndTime'] = $params['serviceEndTime'];
        try{
            $shopInfo = Db::name('shop')->where($where)->update($update);
            if ($shopInfo > 0) {
                $res = $this->getShopStatus($where);
                return [
                    'message' => '修改成功',
                    'info' => $res
                ];
            } else {
                $this->error = '修改失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
    /**
     * @api {post} /app/shop/applywith/apply 10 提现申请
     * @apiName applyWith
     * @apiParam {Float} withdrawalPrice 提现金额
     * @apiParam {Int} withdrawalType 提现方式1微信2支付宝3银行卡
     * @apiParam {String} withdrawalAccount 提现账号
     * @apiParam {String} withdrawalName 收款人姓名
     * @apiGroup appShopGroup
     * @apiVersion 1.0.0
     */
    public function applyWith($params){
        $userInfo = $this->getUserInfo();
        try{
            $params['userId'] = $userInfo['userId'];
            $params['userName'] = $userInfo['userName'];
            $params['userNo'] = $userInfo['userNo'];
            $params['createTime'] = time();
            $params['status'] = 1;
            $res = db('user_withdrawal')->insert($params);
//            $res = Db::name('user_withdrawal')->add($params);
            if($res){
                return [
                    'message'=> '申请成功'
                ];
            }else{
                $this->error = '申请失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }

    }
//    获取当日的开始和结束时间
    public function getDayTime(){
        $begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
        $endtime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
        $data['begintime'] = time($begintime);
        $data['endtime'] = time($endtime);
        return $data;
    }
}