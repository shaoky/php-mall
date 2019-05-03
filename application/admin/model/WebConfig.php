<?php
namespace app\admin\model;
use think\Db;
use think\Model;
/**
 * @apiDefine adminSystemGroup admin-系统管理
 */

/**
 * @api {post} / 1. 配置
 * @apiName ad
 * @apiGroup adminSystemGroup
 * @apiSuccess {Number} configId 主键
 * @apiSuccess {Number} deliverMoney 配送费
 * @apiSuccess {String} servicePhone 客服手机
 * @apiSuccess {String} weixinAccount 客服微信
 * @apiSuccess {Number} freeShippingMoney 满多少免配送费
 * @apiSuccess {Number} autoConfirmDelivery 发货后自动确认收货
 * @apiSuccess {Number} withdrawalCycle 提现周期每月
 * @apiSuccess {Number} orderSettlementCycle 订单结算周期
 * @apiVersion 1.0.0
 */

class WebConfig extends Model {



    /**
     * @api {post} /setting/site/info 1.1 获取网站配置
     * @apiGroup adminSystemGroup
     * @apiVersion 1.0.0
     */

    public function getsite($request)
    {
        try {
            $data['info'] = $this->where('configId', 1)->find();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    /**
     * @api {post} /setting/site/update 1.2 更新网站配置
     * @apiName GetSite
     * @apiGroup adminSystemGroup
     * @apiParam {Number} deliverMoney 配送费
     * @apiParam {String} servicePhone 客服手机
     * @apiParam {String} [weixinAccount] 客服微信
     * @apiParam {Number} freeShippingMoney 满多少免配送费
     * @apiParam {Number} autoConfirmDelivery 发货后自动确认收货
     * @apiParam {Number} withdrawalCycle 提现周期每月
     * @apiParam {Number} orderSettlementCycle 订单结算周期
     * @apiVersion 1.0.0
     */

    public function siteUpdate($request)
    {
        try {
            $request->except('configId');
            $data = $this->where('configId', 1)->update($request->param());
            if ($data == 1) {
                return '更新成功';
            } else {
                // $this->error = '更新失败';
                return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }


    public static function info()
    {
        $data = Db::table('tp_web_config')->find(1);
        return $data;
    }





}
