<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22
 * Time: 10:13
 */

namespace app\admin\model;


use think\Db;
use think\Model;
/**
 * @apiDefine adminCouponCountGroup admin-优惠券模块
 */
class CouponCount extends Model
{
    /**
     * @api {post} /admin/coupon/count/getlist 1. 优惠券统计列表
     * @apiName getList
     * @apiGroup adminCouponCountGroup
     * @apiParam {Number} createdStart 开始时间
     * @apiParam {Number} createdStop 结束时间
     * @apiParam {Number} page 页码
     * @apiParam {Number} size 数量
     * @apiSuccess {Number} consumeNumber 今日消费优惠券数量
     * @apiSuccess {Number} consumeMoney 今日消费优惠券金额
     * @apiSuccess {Number} overdueNumber 今日过期优惠券数量
     * @apiSuccess {Number} overdueMoney 今日过期优惠券金额
     * @apiSuccess {Number} created 日期
     * @apiSuccess {Number} modified 编辑日期
     * @apiSuccess {float} couMoney 优惠券金额
     * @apiSuccess {Number} allGrantCount 优惠券总发放数量
     * @apiSuccess {Float} AllGrantMoney 优惠券总发放金额
     * @apiSuccess {Number} AllConsumeCount 优惠券总消费数量
     * @apiSuccess {Float} AllConsumeMoney 优惠券总消耗金额
     * @apiSuccess {Number} AllOverdueNumber 优惠券总过期数量
     * @apiSuccess {Number} AllOverdueMoney 优惠券总过期金额
     * @apiVersion 1.0.0
     */
    public function getList($params){
        $where = "";
        if (isset($params['createdStart']) && isset($params['createdStop'])){
            $where = "created >= {$params['createdStart']} and created <= {$params['createdStop']}";
        }
        $result['list'] = $this->where($where)->limit($params['page'],$params['size'])->select();
        $result['count'] = $this->where($where)->count();
        $result['common'] = Db::name('couponcommon')->find();
        return $result;
    }
}