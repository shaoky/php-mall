<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 14:35
 */

namespace app\admin\model;


use think\Db;
use think\Model;
/**
 * @apiDefine adminCouponGroup admin-优惠券模块
 */


class Coupon extends Model
{
    /**
     * @api {post} /admin/coupon/index 1. 优惠券列表
     * @apiName getList
     * @apiGroup adminCouponGroup
     * @apiParam {Number} couState 状态 1启用2不启用
     * @apiParam {Number} couRangeKey 范围1全场2分类3单品4品牌
     * @apiParam {Number} page 页码
     * @apiParam {Number} size 数量
     * @apiSuccess {Number} couId 主键
     * @apiSuccess {String} couName 优惠券名称
     * @apiSuccess {Number} couType 优惠券类型1满减
     * @apiSuccess {Number} couAging 优惠券时效 1固定时间2领取后失效
     * @apiSuccess {Number} couStartTime 优惠券开始时间
     * @apiSuccess {Number} couStopTime 优惠券到期时间
     * @apiSuccess {Number} couPrescription 失效
     * @apiSuccess {float} couMoney 优惠券金额
     * @apiSuccess {string} couRuleValue 规则值
     * @apiSuccess {string} couDesc 规则描述
     * @apiSuccess {Number} couRangeKey 规则范围
     * @apiSuccess {string} couRangValue 规则范围值
     * @apiSuccess {string} couPres 优惠券时效整理后 新增时忽略
     * @apiVersion 1.0.0
     */
        public function getList($params){
            try {
//                $where = array();
                $where = "couStatus != 5";
                if ($params['couState'] != 0 ){
//                    $where['couState'] = $params['couState'];
                    $where .= " and couState={$params['couState']}";
                }
                if ($params['couRangeKey'] != 0 ){
//                    $where['couRangeKey'] = $params['couRangeKey'];
                    $where .= " and couRangeKey={$params['couRangeKey']}";
                }
                $res['list'] = Db::name("coupon")->where($where)->limit($params['page'],$params['size'])->select();
                $res['count'] = Db::name("coupon")->where($where)->count();
                for($i=0;$i<count($res['list']);$i++){
                    if ($res['list'][$i]['couAging'] == 1){
//                        固定
                        $res['list'][$i]['couPres'] = date("Y-m-d H:i:s",$res['list'][$i]['couStartTime'])."-".date("Y-m-d H:i:s",$res['list'][$i]['couStopTime']);
                    }else{
                        $res['list'][$i]['couPres'] = $res['list'][$i]['couPrescription']."天";
                    }
                }
                return $res;
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/add 2. 优惠券新增
     * @apiName addCoupon
     * @apiGroup adminCouponGroup
     * @apiParam {String} couName 参考列表返回字段
     * @apiVersion 1.0.0
     */
        public function addCoupon($params){
            if (!$params['couName'] || empty($params['couName'])){
                $this->error = '优惠券名称不能为空';
                return false;
            }

            try {
                $params['couStatus'] = 1;
                $params['couState'] = 1;
                $data = $this->save($params);
                return '添加成功';
            } catch (\Exception $e) {
                // echo $e->getError();
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/update 3. 优惠券编辑
     * @apiName updateCoupon
     * @apiGroup adminCouponGroup
     * @apiParam {String} couName 参考列表返回字段
     * @apiVersion 1.0.0
     */
        public function updateCoupon($params){
            try{
//                if(isset($params['couAging']) && $params['couAging'] == 1){
//                    $a = explode("-",$params['couPres']);
//                    $params['couStartTime'] = $a[0];
//                    $params['couStopTime'] = $a[1];
//                    unset($params['couPres']);
//                }
//                if (isset($params['couAging']) && $params['couAging'] == 2){
//                    $params['couPrescription'] = $params['couPres'];
//                    unset($params['couPres']);
//                }
                if (isset($params['couPres'])){
                    unset($params['couPres']);
                }

                $data = Db::name('coupon')->where('couId',$params['couId'])->update($params);
                if ($data == 1) {
                    return '更新成功';
                }else{
                    return '更新失败';
                }
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/update/state 3. 优惠券状态修改
     * @apiName updateState
     * @apiGroup adminCouponGroup
     * @apiParam {Number} couId Id
     * @apiParam {Number} couState 状态1启用2不启用
     * @apiVersion 1.0.0
     */
        public function updateState($params){
            try{
                $data = Db::name('coupon')->where('couId',$params['couId'])->update($params);
                if ($data == 1) {
                    return '更新成功';
                }else{
                    return '更新失败';
                }
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/delete 3. 优惠券删除
     * @apiName deleteCoupon
     * @apiGroup adminCouponGroup
     * @apiParam {Number} couId Id
     * @apiVersion 1.0.0
     */
        public function deleteCoupon($params){
            try{
                $update['couStatus'] = 5;
                $data = Db::name('coupon')->where('couId',$params['couId'])->update($update);
                if ($data == 1) {
                    return '更新成功';
                }else{
                    return '更新失败';
                }
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/getattrgoods 3. 获取商品分类品牌
     * @apiName getAttrGoods
     * @apiGroup adminCouponGroup
     * @apiParam {Number} type 2分类4品牌3单品
     * @apiVersion 1.0.0
     */
    public function getAttrGoods($params){
        $arr['list'] = array();
            if ($params['type'] == 2){
//                分类
//                if ($params['parentId'] == 0){
//                    一级分类
//
//                }
                $res = Db::name('goods_type')->select();
                for($i=0;$i<count($res);$i++){
                    $arr['list'][$i]['id'] = $res[$i]['goodsClassId'];
                    $arr['list'][$i]['name'] = $res[$i]['goodsClassName'];
                }
            }
            if ($params['type'] == 4){
//                品牌
                $res = Db::name('goods_brand')->select();
                for($i=0;$i<count($res);$i++){
                    $arr['list'][$i]['id'] = $res[$i]['brandId'];
                    $arr['list'][$i]['name'] = $res[$i]['brandName'];
                }
            }
            if ($params['type'] == 3){
//                单品
                $where['isMemberGoods'] = 0;
                $where['isOpen'] = 1;
                $res = Db::name('goods')->where($where)->select();
                for($i=0;$i<count($res);$i++){
                    $arr['list'][$i]['id'] = $res[$i]['goodsId'];
                    $arr['list'][$i]['name'] = $res[$i]['goodsName'];
                }
            }
        return $arr;
    }


    /**
     * @api {post} /admin/coupon/count/list 4. 优惠券统计列表
     * @apiName getCouponCountList
     * @apiGroup adminCouponGroup
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
    public function getCouponCountList($params){
            $timestart = $params['timeStart'];
            $timestop = $params['timeStop'];
            $times = $timestop-$timestart;
            $days = $times/60/60/24;
            $nextTime = $timestop;
        try {
            $list = Db::name('couuser')->alias('a')
            // ->group('DAY(FROM_UNIXTIME(createTime, \'%Y-%m-%d\'))')
            ->join('coupon b', 'a.couId = b.couId')
            ->field('a.*, b.*')
            ->select();
            $data['common']['allGrantCount'] = 0; // 总发放优惠券数量
            $data['common']['allGrantMoney'] = 0; // 总发放优惠券金额
            $data['common']['allConsumeCount'] = 0; // 总发消耗数量
            $data['common']['allConsumeMoney'] = 0; // 总发消耗金额
            $data['common']['allOverdueNumber'] = 0; // 总过期数量
            $data['common']['allOverdueMoney'] = 0; // 总过期金额
            for ($i=0; $i < $days; $i++) { 
                $date = date('Y-m-d', $nextTime);
                $result[$i]['date'] = $date;
                $result[$i]['grantNumber'] = 0;
                $result[$i]['couMoney'] = 0;
                $result[$i]['consumeNumber'] = 0;
                $result[$i]['consumeMoney'] = 0;
                $result[$i]['overdueNumber'] = 0;
                $result[$i]['overdueMoney'] = 0;
                foreach($list as $item) {
                    $createTime = date('Y-m-d', $item['createTime']);
                    if ($date == $createTime) {
                        // 总共发放
                        $result[$i]['grantNumber']++;
                        $result[$i]['couMoney'] += $item['couMoney'];
                        $data['common']['allGrantCount'] ++;
                        $data['common']['allGrantMoney'] += $item['couMoney'];
                        // 使用的优惠券
                        if ($item['cuStatus'] == 3 || $item['cuStatus'] == 4) {
                            $result[$i]['consumeNumber']++;
                            $result[$i]['consumeMoney'] += $item['couMoney'];
                            $data['common']['allConsumeCount'] = 0;
                            $data['common']['allConsumeMoney'] = 0;
                        }
                        // 过期的优惠券
                        if ($item['couAging'] == 1) {
                            if ($item['couStopTime'] < time()) {
                                $result[$i]['overdueNumber']++;
                                $result[$i]['overdueMoney'] += $item['couMoney'];
                                $data['common']['allOverdueNumber'] = 0;
                                $data['common']['allOverdueMoney'] = 0;
                            }
                        }
                        if ($item['couAging'] == 2) {
                            if ($item['overTime'] < time()) {
                                $result[$i]['overdueNumber']++;
                                $result[$i]['overdueMoney'] += $item['couMoney'];
                                $data['common']['allOverdueNumber'] = 0;
                                $data['common']['allOverdueMoney'] = 0;
                            }
                        }
                    }
                }
                $nextTime = $nextTime - 60*60*24;
            }

            $data['list'] = $result;
           

            // $list1 = Db::name('couuser')->alias('a')
            // ->group("DAY(FROM_UNIXTIME(createTime))")
            // ->where('cuStatus', 'in', [3, 4])
            // ->join('coupon b', 'a.couId = b.couId')
            // ->field('a.createTime, SUM(b.couMoney) as couMoney, COUNT(a.cuId) as grantNumber')
            // ->select();
            // $list1 = Db::name('couuser')->alias('a')
            // ->group("DAY(FROM_UNIXTIME(createTime))")
            // ->where('cuStatus', 'in', [3, 4])
            // ->join('coupon b', 'a.couId = b.couId')
            // ->field('a.createTime, SUM(b.couMoney) as couMoney, COUNT(a.cuId) as grantNumber')
            // ->select();
            // dump($list);
            // dump($list1);
            // dump($list);
            // foreach($list as $key => &$item) {
            //     $createTime = date("Y-m-d",$item['createTime']);

            //     // dump($createTime);
            //     $data['list'][$createTime][] = $item;
            // }
            // foreach($list1 as $key1 => &$item) {
            //     $item['consumeNumber'] = $list1[$key]['grantNumber'];
            //     $item['consumeMoney'] = $list1[$key]['couMoney'];
            // }
            // $data['list'] = $list;
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
       
    }

}