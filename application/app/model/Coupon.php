<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:22
 */

namespace app\app\model;


use app\h5\model\Common;
use think\Db;

/**
 * @apiDefine appCouponGroup app-优惠券模块
 */
class Coupon extends Common
{
    /**
     * @api {post} /app/coupon/selfcoupon 1. 个人优惠券列表
     * @apiName getSelfCoupon
     * @apiGroup appCouponGroup
     * @apiParam {Number} page = 1 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {Number} couStatus 1 '未使用', 2'已使用', 3'已过期'
     * @apiSuccess {Array} list
     * @apiVersion 1.0.0
     */
    public function getSelfCoupon($params){
        $user = $this->getUserId();

        switch ($params['couStatus']){
            case 1:
                $cuStatus = 2;
                $where[] = ['a.cuStatus', '=', $cuStatus];
                break;
            case 2:
                $cuStatus = 4;
                $where[] = ['a.cuStatus', '=', $cuStatus];
                break;
            case 3:
                $cuStatus = 6;
                break;
        }
        
        $where[] = ['a.userId', '=', $user];
        // if ($couStatus == 2) {
        //     $where[] = ['b.couStopTime', '>', time()];
        // }
        // if ($couStatus == 6) {
        //     $where[] = ['b.couStopTime', '<', time()];
        // }

        $list = Db::name('couuser')
            ->alias('a')
            ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
            ->where($where)
            ->order('b.couMoney')
            ->page($params['page'],$params['size'])
            ->select();
        $res['list'] = [];
        foreach($list as $key => &$item) {
            $add = false;
            if ($cuStatus == 2 && $item['couAging'] == 1) {
                if ($item['couStopTime'] > time()) {
                    $res['list'][] = $item;
                    $add = true;
                }
            }
            if ($cuStatus == 2 && $item['couAging'] == 2) {
                if ($item['overTime'] > time()) {
                    $res['list'][] = $item;
                    $add = true;
                }
            }
            if ($cuStatus == 4 && $item['cuStatus'] == 4) {
                $res['list'][] = $item;
                $add = true;
            }
            if ($add) {
                $res['list'][$key]['couRangeKeyName'] = getStatusName('couRangeKey', $item['couRangeKey']);
                $res['list'][$key]['couText'] = '满'.$item['couRuleValue'].'减'.$item['couMoney'];
            }
            
        }
        $res['count'] = Db::name('couuser')
            ->alias('a')
            ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
            ->where($where)
            ->count();
        return $res;
    }
    /**
     * @api {post} /app/coupon/usableCoupon 2. 订单可用优惠券列表
     * @apiName getUsableCoupon
     * @apiGroup appCouponGroup
     * @apiParam {Number} page = 1 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} list
     * @apiSuccess {Boolean} .isUse 是否可用， true可以用，false不可用
     * @apiVersion 1.0.0
     */
    public function getUsableCoupon($params){
        $user = $this->getUserInfo();
        
        try {
            $config = Db::name('web_config')->find();
            $data['goodsList'] = Db::name('cart')->alias('a')
                ->join('goods b', 'a.goodsId = b.goodsId')
                ->field('a.goodsNum, b.*')
                ->where([ 
                    'userId' => $user['userId'],
                    'isSelected' => 1
                ])
                ->select();

            $data['totalMoney'] = 0;
            $isFreeShipping = 0;
            foreach($data['goodsList'] as $key=>$item) {
                if ($item['isFreeShipping'] == 1) {
                    $isFreeShipping = 1;
                }
                if ($user['userType'] == 1) {
                    $data['totalMoney'] += $item['shopPrice'] * $item['goodsNum'];
                    $data['goodsList'][$key]['goodsPrice'] = $item['shopPrice'];
                } else {
                    $data['totalMoney'] += $item['memberPrice'] * $item['goodsNum'];
                    $data['goodsList'][$key]['goodsPrice'] = $item['memberPrice'];
                }
            }

            if ($isFreeShipping == 1) {
                $data['deliverMoney'] = 0;
                $data['payMoney'] = $data['totalMoney'];
            } else {
                if ($data['totalMoney'] < $config['deliverMoney']) {
                    $data['deliverMoney'] = $config['deliverMoney'];
                    $data['payMoney'] = $data['totalMoney'] +  $data['deliverMoney'];
                } else {
                    $data['deliverMoney'] = 0;
                    $data['payMoney'] = $data['totalMoney'];
                }
            }

            $where = array();
            $where[] = ['a.cuStatus', '=', 2];
            $where[] = ['a.userId', '=', $user['userId']];
            // $where[] = ['b.couStopTime', '>', time()];
            $res['list'] = Db::name('couuser')
            ->alias('a')
            ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
            ->where($where)
            ->order('couMoney')
            // ->limit($params['page'],$params['size'])
            ->select();
            
            $res['count'] = Db::name('couuser')
                ->alias('a')
                ->join(['tp_coupon'=>'b'],'a.couId=b.couId')
                ->where($where)
                ->count();
            // dump($res['list']);
            foreach($res['list'] as $key => &$item) {
                $item['couRangeKeyName'] = getStatusName('couRangeKey', $item['couRangeKey']);
                $item['couText'] = '满'.$item['couRuleValue'].'减'.$item['couMoney'];
                if (time() < $item['startTime'] && time() > $item['overTime']) {
                    $item['isUse'] = false;
                    continue;
                }
                if ($item['couRangeKey'] == 1) {
                    // echo $item['couRuleValue'].'+'.$data['totalMoney'];
                    if ($item['couRuleValue'] <= $data['totalMoney']) {
                        $item['isUse'] = true;
                    } else {
                        $item['isUse'] = false;
                    }
                }

                if ($item['couRangeKey'] == 3) { // 单品
                    $cartList = Db::name('cart')->where(['userId' => $user['userId'], 'isSelected' => 1])->select();
                    foreach($cartList as $key1 => &$item1) {
                        if ($item1['goodsId'] == $item['couRangValue']) {
                            $item['isUse'] = true;
                        } else {
                            $item['isUse'] = false;
                        }
                    }
                }
            }
            return $res;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /app/coupon/exchange 3. 个人优惠券兑换码兑换
     * @apiName ExchangeCoupon
     * @apiGroup appCouponGroup
     * @apiParam {Number} redeemCode 兑换码
     * @apiVersion 1.0.0
     */
    public function ExchangeCoupon($params, $userId = 0) {
        if ($userId == 0) {
            $userId = $this->getUserId();
        } else {
            $userId = $userId;
        }

        Db::startTrans();
        try {
            if (!empty($params['idCode'])) {
                $params['isCode'] = 'becomeMember';
                $infoActive = Db::name('coupon_active')->where(["idCode" =>$params['idCode'], "caStatus" => 1])->find();
                $res = Db::name('coupon_active_redeem')->where([
                    ['caId', '=', $infoActive['caId']],
                    ['carStatus', '=', 1]
                ])->limit(1)->find();
                if ($res == null) {
                    return [
                        'error' => '优惠券已经没有了'
                    ];
                }
            }

            if (empty($params['idCode'])) {
                $redeemCode = $params['redeemCode'];
                $res = Db::name('coupon_active_redeem')->where("redeemCode",$params['redeemCode'])->find();
                if (empty($res)){
                    $this->error = '兑换码不正确';
                    return;
                }
                if ($res['carStatus'] == 2) {
                    $this->error = '兑换码已失效';
                    return;
                }
            }

            // 更新优惠券兑换码的状态
            
            $update['carStatus'] = 2;
            $update['userId'] = $userId;
            $update['updateTime'] = time();
            Db::name('coupon_active_redeem')->where("redeemCode", $res['redeemCode'])->update($update);
            // 更新领取后的库存
            Db::name('coupon_active')->where('caId',$res['caId'])
            ->inc('userQueryNum', 1)
            ->dec('caStock', 1)
            ->update();
            // 更新增优惠券用户的关系
            $active = Db::name('coupon_active')->where('caId',$res['caId'])->find();
            $coupon = explode(",",$active['caCou']);
            foreach($coupon as $item) {
                $data['couId'] = $item;
                $data['userId'] = $userId;
                $data['cuStatus'] = 2;
                $data['caId'] = $res['caId'];
                $data['createTime'] = time();
                $coupon = Db::name('coupon')->where('couId', $item)->find();
                // 固定时间
                if ($coupon['couAging'] == 1) {
                    $data['startTime'] = $coupon['couStartTime'];
                    $data['overTime'] = $coupon['couStopTime'];
                }
                // 领取后失效
                if ($coupon['couAging'] == 2) {
                    $data['startTime'] = time();
                    $data['overTime'] = strtotime(date("Y-m-d H:i:s", strtotime('+'.$coupon['couPrescription'] .'day')));
                }
                Db::name('couuser')->data($data)->insert();
            }
            //优惠券集合
            // $grantMoney = 0;
            // $grantNumber =0;
            // for($b = 0;$b<count($coupon);$b++){
            //     $d = Db::name('coupon')->where("couId",$coupon[$b])->find();
            //     $grantMoney = $grantMoney+$d['couMoney'];
            //     $grantNumber = $grantNumber + $active['caNumber'];
            // }
            // $count = Db::name('couponcount')->where("created",date("Y-m-d"))->find();
            // if (!empty($count)){
            //     $arr['grantNumber'] = $count['grantNumber']+$grantNumber;
            //     $arr['grantMoney'] = $count['grantMoney']+$grantMoney;
            //     Db::name('couponcount')->data($arr)->where("created",date("Y-m-d"))->update();
            // }else{
            //     $arr['grantNumber'] = $count['grantNumber']+$grantNumber;
            //     $arr['grantMoney'] = $count['grantMoney']+$grantMoney;
            //     $arr['created'] = date("Y-m-d");
            //     Db::name('couponcount')->data($arr)->insert();
            // }
            // 优惠券总数
            // $common = Db::name('couponcommon')->find();
            // if (!empty($common)){
                // 修改
                // $commarr['allGrantCount'] = $grantNumber + $common['allGrantCount'];
                // $commarr['AllGrantMoney'] = $grantMoney + $common['AllGrantMoney'];
                // Db::name('couponcommon')->data($commarr)->where("id",$common['id'])->update();
            // }else{
                // 新增
                // $commarr['allGrantCount'] = $grantNumber;
                // $commarr['AllGrantMoney'] = $grantMoney;
                // Db::name('couponcommon')->data($commarr)->insert();
            // }
            Db::commit();
            return [
                'message' => '兑换成功'
            ];
        } catch (\Exception $e) {
            trace('订单支付，优惠券赠送：'.$e->getMessage(), 'error');
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
        
    }

//    定时检查优惠券是否到期
    public function CheckCoupon(){
        $time = time();
        $where = 'couAging = 1 and (couStatus = 1 or couStatus = 2 or couStatus = 3)';
        $res = Db::name('coupon')->where($where)->select();
        for($i=0;$i<count($res); $i++) {
//            $overdueMoney = 0;
//            $overdueNumber =0;
//            if (!empty($res[$i]['couStopTime'])){
//            if ($res[$i]['couAging'] == 1){
                $couStatus['couStatus'] = 6;
//                if ($res[$i]['couAging'] == 2){
//                    $res[$i]['couStopTime'] =$res[$i]['couStopTime']+$res[$i]['couPrescription']*60*60*24;
//                }
                    if ($res[$i]['couStopTime'] < $time){
//                    优惠券表
                        Db::name('coupon')->data($couStatus)->where("couId",$res[$i]['couId'])->update();
                    }
                /*$grantMoney = $overdueMoney+$res[$i]['couMoney'];
                $grantNumber = $overdueNumber + 1;
                $count = Db::name('couponcount')->where("created",date("Y-m-d"))->find();
                if (!empty($count)){
                    $arr['overNumber'] = $count['overNumber']+$grantNumber;
                    $arr['overMoney'] = $count['overMoney']+$grantMoney;
                    Db::name('couponcount')->data($arr)->where("created",date("Y-m-d"))->update();
                }else{
                    $arr['overNumber'] = $count['overNumber']+$grantNumber;
                    $arr['overMoney'] = $count['overMoney']+$grantMoney;
                    $arr['created'] = date("Y-m-d");
                    Db::name('couponcount')->data($arr)->insert();
                }
//            优惠券总数
                $common = Db::name('couponcommon')->find();
                if (!empty($common)){
//                修改
                    $commarr['AllOverdueNumber'] = $grantNumber + $common['AllOverdueNumber'];
                    $commarr['AllOverdueMoney'] = $grantMoney + $common['AllOverdueMoney'];
                    Db::name('couponcommon')->data($commarr)->where("id",$common['id'])->update();
                }else{
//                新增
                    $commarr['allGrantCount'] = $grantNumber;
                    $commarr['AllGrantMoney'] = $grantMoney;
                    Db::name('couponcommon')->data($commarr)->insert();
                }*/
//            }else{
//                $couponUser = Db::name('couuser')->where()->select();
//            }
        }
        $where1 = 'couStatus = 1 or couStatus = 2 or couStatus = 3';
//        $res1 = Db::name('coupon')->where($where1)->select();
        $couponUser = Db::name('couuser')->where($where1)->select();
        for ($a = 0;$a < count($couponUser);$a++){
            $overdueMoney = 0;
            $overdueNumber =0;
            $couStatus = 6;
            if ($couponUser[$a]['overTime'] < $time) {
//                    优惠券用户关系表
                Db::name('couuser')->where('couId', $couponUser[$a]['couId'])->update($couStatus);
                $Coupon = Db::name('coupon')->where('couId',$couponUser[$a]['couId'])->find();
                $grantMoney = $overdueMoney + $Coupon['couMoney'];

                $grantNumber = $overdueNumber + 1;
                $count = Db::name('couponcount')->where("created", date("Y-m-d"))->find();
                if (!empty($count)) {
                    $arr['overNumber'] = $count['overNumber'] + $grantNumber;
                    $arr['overMoney'] = $count['overMoney'] + $grantMoney;
                    Db::name('couponcount')->data($arr)->where("created", date("Y-m-d"))->update();
                } else {
                    $arr['overNumber'] = $count['overNumber'] + $grantNumber;
                    $arr['overMoney'] = $count['overMoney'] + $grantMoney;
                    $arr['created'] = date("Y-m-d");
                    Db::name('couponcount')->data($arr)->insert();
                }
//            优惠券总数
                $common = Db::name('couponcommon')->find();
                if (!empty($common)) {
//                修改
                    $commarr['AllOverdueNumber'] = $grantNumber + $common['AllOverdueNumber'];
                    $commarr['AllOverdueMoney'] = $grantMoney + $common['AllOverdueMoney'];
                    Db::name('couponcommon')->data($commarr)->where("id", $common['id'])->update();
                } else {
//                新增
                    $commarr['allGrantCount'] = $grantNumber;
                    $commarr['AllGrantMoney'] = $grantMoney;
                    Db::name('couponcommon')->data($commarr)->insert();
                }
            }
        }
    }

/*//    读取日志
public function getLog(){
//        var_dump();die;
        $file = date("Ymd");
        $path = SVN_ROOT."/runtime/log/$file".".log";
        $res = file_get_contents($path);
//    $res = rtrim($res,"\"} ");
        $res = ltrim($res,"\"{");
    $res = substr($res,0,strlen($res)-4);
//    var_dump($res);
        $a = explode("\"}",$res);
        for($b=0;$b<count($a);$b++){
            $a[$b] = "{\"".$a[$b]."\"}";
        }
        var_dump();
        var_dump(count($a));
}*/
}