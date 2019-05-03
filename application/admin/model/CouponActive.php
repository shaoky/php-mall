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
 * @apiDefine adminCouponActiveGroup admin-优惠券活动模块
 */


class CouponActive extends Model
{
    /**
     * @api {post} /admin/coupon/active/index 1. 优惠券活动表
     * @apiName getList
     * @apiGroup adminCouponActiveGroup
     * @apiParam {Number} couState 状态 1启用2不启用
     * @apiParam {Number} couRangeKey 范围1全场2分类3单品
     * @apiParam {Number} page 页码
     * @apiParam {Number} size 数量
     * @apiSuccess {Number} caId 主键
     * @apiSuccess {String} caName 活动名称
     * @apiSuccess {Number} caStartTime 活动开始时间
     * @apiSuccess {Number} caStopTime 活动到期时间
     * @apiSuccess {float} caOut 发放渠道1：app 2：h5
     * @apiSuccess {Number} caStatus 活动状态1未领取2待使用3使用中4已使用5已删除6已过期
     * @apiSuccess {string} caDesc 规则描述
     * @apiSuccess {Number} caRangeKey 规则范围
     * @apiSuccess {string} caRedeem 兑换码集合
     * @apiSuccess {Number} caNumber 活动数量
     * @apiSuccess {Number} caCouCount 优惠券类型数量  新增和编辑额时候该字段不需要传
     * @apiSuccess {Number} userQueryNum 用户获取数量
     * @apiSuccess {String} idCode 标识码
     * @apiSuccess {String} caStock 库存
     * apiSuccess {Number} caQuery 已领取数量 新增的时候该字段不需要传
     * @apiSuccess {Number} caState 状态1启用2不启用
     * @apiVersion 1.0.0
     */
        public function getList($params){
            try {
                $where = "caStatus != 4";
                $res['list'] = Db::name("coupon_active")->where($where)->limit($params['page'],$params['size'])->select();
                $res['count'] = Db::name("coupon_active")->where($where)->count();
                for($i=0;$i<count($res['list']);$i++){
                    $a = explode(",",$res['list'][$i]['caCou']);
                    $res['list'][$i]['caCouCount'] = count($a);
//                    $res['list'][$i]['caQuery'] = $res['list'][$i]['caNumber'] - $res['list'][$i]['caStock'];
                }
                return $res;
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }

        }
    /**
     * @api {post} /admin/coupon/active/add 2. 优惠券活动新增
     * @apiName addCouponActive
     * @apiGroup adminCouponActiveGroup
     * @apiParam {String} caName 参考列表返回字段
     * @apiVersion 1.0.0
     */
        public function addCouponActive($params){
            if (empty($params['caName']) || !isset($params['caName'])){
                $this->error = "活动名称不能为空";
                return false;
            }
            // if($params['caNumber'] > 490){
            //     $this->error = "活动数量超出最大限制！最大限制为490";
            //     return false;
            // }
            $code = array();
            for($i=0;$i < $params['caNumber'];$i++){
                $code[$i] = $this->msectime();
            }
            $params['caRedeem'] = json_encode($code);
            $params['caCreate'] = time();
            $params['caModified'] = time();
            $params['caStatus'] = 1;
            $params['caState'] = 1;
            $params['caStock'] = $params['caNumber'];;
            $params['caNumber'] = $params['caNumber'];;
            try {
                $data = $this->insertGetId($params);
                for($a = 0; $a < $params['caNumber']; $a++) {
                    $obj['caId'] = $data;
                    $obj['redeemCode'] = $code[$a];
                    $obj['carStatus'] = 1;
                    $obs['createTime'] = time();
                    Db::name('coupon_active_redeem')->data($obj)->insert();
                }

                if ($data > 0 ){
                    return '添加成功';
                }else{
                    return '添加失败';
                }

            } catch (\Exception $e) {
                // echo $e->getError();
//                MySQL::callBack();
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/active/update 3. 优惠券活动新编辑
     * @apiName updateCouponActive
     * @apiGroup adminCouponActiveGroup
     * @apiParam {String} caName 参考列表返回字段
     * @apiVersion 1.0.0
     */
        public function updateCouponActive($params){
            Db::startTrans();
            try{
                $result = $this->where("caId",$params['caId'])->find();
                $params['caModified'] = time();
                if ($params['caNumber'] != $result['caNumber']) {
                    if ($params['caNumber'] > $result['caNumber']){
                        // 增加：假设原仓库有90，计算：新仓库（190） = 目标值（200） - 已领次数（10）
                        $params['caStock'] = $params['caNumber'] - $result['userQueryNum'];
                        // 循环需要加N次
                        $addNumber = $params['caNumber'] - $result['caStock'] - $result['userQueryNum'];
                        if ($addNumber > 10000) {
                            $this->error = '添加数量不能超过10000，超过请多次添加';
                            return;
                        }
                        for($a = 0; $a < $addNumber; $a++) {
                            $obj['caId'] = $params['caId'];
                            $obj['createTime'] = time();
                            $obj['redeemCode'] = $this->msectime();
                            $obj['carStatus'] = 1;
                            Db::name('coupon_active_redeem')->insert($obj);
                        }
                    } else {
                        // 减少：假设原仓库有90，已领10 = 100总合数量  计算：新库存（80） = 总和数量（90）- 已领的次数（10）
                        $params['caStock'] = $params['caNumber'] - $result['userQueryNum'];
                        // 需要删除的数量 假设：库存（90） - 目标值（80）
                        $delNumber = $result['caNumber'] - $params['caNumber'];
                        if ($delNumber > $result['caStock']) {
                            $this->error = '数量减少的值不能大于已领的数量';
                            return;
                        }
                        Db::name('coupon_active_redeem')->where([
                            ['caId', '=', $params['caId']],
                            ['carStatus', '=', 1]
                        ])->order('carId', 'desc')->limit($delNumber - 1)->delete();
                    }
                }

                $data = $this->where("caId",$params['caId'])->update($params);
                Db::commit();
                if ($data == 1) {
                    // if ($status == 'add'){
                    //     $addWhere['caId'] = $params['caId'];
                    //     $addWhere['carStatus'] = 1;
                    //     $redeemup['carStatus'] = 1;
                    //     $a = Db::name('coupon_active_redeem')->where($addWhere)->update($redeemup);
                    //     if ($a > 0 ){
                    //         $code = array();
                    //         for($i=0;$i < $params['caStock'];$i++){
                    //             $code[$i] = $this->msectime();
                    //         }
                    //         for($b = 0; $b < $params['caStock']; $b++) {
                    //             $obj['caId'] = $params['caId'];
                    //             $obj['redeemCode'] = $code[$b];
                    //             $obj['carStatus'] = 1;
                    //             db('coupon_active_redeem')->data($obj)->insert();
                    //         }
                    //     }
                    // }

                    return '更新成功';
                }else{
                    return '更新失败';
                }
            }catch (\Exception $e){
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }
/**
     * @api {post} /admin/coupon/active/update/status 4. 优惠券活动状态修改
     * @apiName updateStatusActive
     * @apiGroup adminCouponActiveGroup
     * @apiParam {Number} caId Id
     * @apiParam {Number} caState 状态1启用2不启用
     * @apiVersion 1.0.0
     */
        public function updateStatusActive($params){
            try{
                $params['caModified'] = time();
//                $arr['caStatus'] =
                $data = Db::name('coupon_active')->data($params)->where("caId",$params['caId'])->update();
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
     * @api {post} /admin/coupon/active/delete 5. 优惠券活动删除
     * @apiName deleteActive
     * @apiGroup adminCouponActiveGroup
     * @apiParam {Number} caId Id
     * @apiVersion 1.0.0
     */
        public function deleteActive($params){
            try{
                $arr['caModified'] = time();
                $arr['caStatus'] = 4;
                $data = $this->where("caId",$params['caId'])->update($arr);
                $re['carStatus'] = 3;
                Db::name('coupon_active_redeem')->where('caId',$params['caId'])->update($re);
                if ($data == 1) {
                    return '删除成功';
                }else{
                    return '删除失败';
                }
            }catch (\Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }
    /**
     * @api {post} /admin/coupon/active/getredeem 6. 优惠券活动兑换码列表
     * @apiName getRedeem
     * @apiGroup adminCouponActiveGroup
     * @apiParam {String} redeemCode 兑换码
     * @apiParam {Number} caId id
     * @apiParam {Number} page 页码
     * @apiParam {Number} size 数量
     * @apiVersion 1.0.0
     */
    public function getRedeem($params){
        if (isset($params['redeemCode'])){
            $like = "%".$params['redeemCode']."%";
        }else{
            $like = "%%";
        }
//        $res['list'] = Db::name('coupon_active_redeem')
//            ->alias("a")
//            ->join("user b","a.userId=b.userId")
//            ->where('a.caId',$params['caId'])
//            ->whereLike('a.redeemCode',$like)
//            ->limit($params['page'],$params['size'])
//            ->select();
//        $res['count'] = Db::name('coupon_active_redeem')
//            ->alias("a")
//            ->join("user b","a.userId=b.userId")
//            ->where('a.caId',$params['caId'])
//            ->whereLike('a.redeemCode',$like)
//            ->count();
        $where = "caId = {$params['caId']} and carStatus != 3";
            $res['list'] = Db::name('coupon_active_redeem')
            ->where($where)
            ->whereLike('redeemCode',$like)
            ->page($params['page'],$params['size'])
            ->select();
        $res['count'] = Db::name('coupon_active_redeem')
            ->where($where)
            ->whereLike('redeemCode',$like)
            ->count();
        for ($i=0;$i<count($res['list']);$i++){
            $userId = $res['list'][$i]['userId'];
            if ($userId == 0){
                $res['list'][$i]['userName'] = "";
            }else{
                $user = Db::name('user')->where('userId',$userId)->find();
                $res['list'][$i]['userName'] = $user['loginName'];
            }
        }
        return $res;
    }
    //返回当前的毫秒时间戳
    public function msectime() {
//        list($msec, $sec) = explode(' ', microtime());
//        $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        $str = "1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        str_shuffle($str);
        $name=substr(str_shuffle($str),26,10);
        return $name;
    }
}