<?php
namespace app\admin\model;
use think\Db;
use app\admin\model\Common;
use app\app\model\WxApi;
use app\h5\model\WxApi as WxApiH5;
use app\app\model\Alipay;
/**
 * @apiDefine adminOrderRefundGroup admin-退货模块
 */

/**
 * @api {post} / 1. 订单退货表
 * @apiName orderRefund
 * @apiGroup adminOrderRefundGroup
 * @apiSuccess {Number} refundId 主键
 * @apiSuccess {Number} orderId 订单id
 * @apiSuccess {String} refundRemark 退款说明
 * @apiSuccess {Number} refundMoney 退款金额
 * @apiSuccess {Number} refundStatus 退货状态：1退款中,2已退款,3已拒绝
 * @apiSuccess {String} refundReason 退款理由：1拍错了,2不想买了,3商品无货,4其他原因
 * @apiSuccess {Number} refundType 退款类型：1仅退款,2退货退款
 * @apiSuccess {String} refundImage 退款图片
 * @apiSuccess {String} refuseReason 拒绝原因
 * @apiSuccess {Number} createTime 创建时间
 * @apiSuccess {Number} resolveTime 处理时间
 * @apiVersion 1.0.0
 */

class OrderRefund extends Common {

    public function getOrderRefundList($request)
    {

        /**
         * @api {post} /admin/order/refund/list 1.1 退款列表
         * @apiName adminOrderRefundList
         * @apiGroup adminOrderRefundGroup
         * @apiParam {String} name  收货人
         * @apiParam {String} username  用户名
         * @apiVersion 1.0.0
         */

        $where =[];
        if ($request->has('begintime') && $request->has('endtime')) {
            array_push($where,['a.createTime', '>=' ,$request->post('begintime')]);
            array_push($where,['a.createTime', '<=' ,$request->post('endtime')]);
        }

       if ($request->has('userName'))
        {
            if (is_numeric($request->post('userName')))
                array_push($where,['c.userNo', '=' ,$request->post('userName')]);
            else
                array_push($where,['c.userName', 'like' ,'%'.$request->post('userName').'%']);
        }

        if ($request->has('refundStatus'))
        {
            array_push($where,['a.refundStatus', '=' ,$request->post('refundStatus')]);
        }

        if ($request->has('orderNo'))
        {
            array_push($where,['a.orderNo', 'like' ,'%'.$request->post('orderNo').'%']);
        }
        try {
            $data['list'] = $this->alias('a')
                ->where($where)
                ->leftjoin('order b','a.orderNo = b.orderNo')
                ->join('user c','c.userId = a.userId')
                ->page($request->post('page',1), $request->post('size',20))
                ->order('a.createTime','desc')
                ->field('a.*,b.userName as name, c.userName')
                ->select();
            $data['count'] = $this->alias('a')
                ->where($where)
                // ->leftjoin('order b','a.orderId = b.orderId')
                ->count();

            foreach ($data['list'] as $item) {
                $item['refundStatusName'] = getStatusName('refundStatus',$item['refundStatus']);
                $item['refundTypeName'] = getStatusName('refundType',$item['refundType']);
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return $data;

    }

    /**
     * @api {post} /admin/order/refund/add 1.2 添加退款
     * @apiName adminOrderRefundupdate
     * @apiGroup adminOrderRefundGroup
     * @apiParam {Number} refundId  退款Id
     * @apiParam {Number} refundStatus  退货状态
     * @apiParam {String} [refuseReason]  拒绝原因
     * @apiVersion 1.0.0
     */

    public function OrderRefundupdate($params)
    {
        $update = [];
        if (!empty($params['refundStatus'])) {
            $update['refundStatus'] = $params['refundStatus'];
        }
        if (!empty($params['refundImage'])) {
            $update['refundImage'] = $params['refundImage'];
        }
        if (!empty($params['refuseReason'])) {
            $update['refuseReason'] = $params['refuseReason'];
        } else {
            $update['refuseReason'] = '';
        }
        $update['resolveTime'] = time();

        Db::startTrans();
        try {
            $data = Db::name('order_refund')->where('refundId', $params['refundId'])->update($update);
            if (!empty($params['refundStatus'])) {
                if ($params['refundStatus'] == 3) {
                    $refund = Db::name('order_refund')->where('refundId', $params['refundId'])->find();
                    Db::name('order')->where('orderId', $refund['orderId'])->update(['orderStatus' => $refund['orderStatus']]);
                }
            }

            Db::commit();
            if ($data == 1) {
                return '更新成功';
            } else {
                // $this->error = '更新失败';
                return '更新失败';
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }


    }

    /**
     * @api {post} /admin/order/refund/info 1.3 退款信息
     * @apiName  adminOrderRefundInfo
     * @apiGroup adminOrderRefundGroup
     * @apiParam {Number} refundId  退款Id
     * @apiVersion 1.0.0
     */

    public function OrderRefundInfo($request)
    {
        if (!$request->has('refundId'))
        {
            return $this->error = '没有退款Id';
        }
        try {
            $data['info'] = $this->where('refundId',$request->refundId)->find();

            if (empty($data['info'])){
                return $this->error = '没有查找到对应的退款记录';
            }
            $data['info']['refundStatusName'] = getStatusName('refundStatus',$data['info']['refundStatus']);
            $data['info']['refundTypeName'] = getStatusName('refundType',$data['info']['refundType']);
            $data['info']['goodsList'] = db('orderGoods')->where(['orderId' => $data['info']['orderId']])->select();
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $data;

    }

    public function setRefund ($params) {
        Db::startTrans();
        try {
            $where = [
                'orderId' => $params['orderId']
            ];
            $order = Db::name('order')->where($where)->find();
            if ($order['orderStatus'] == 5) {
                $data = Db::name('order')->where($where)->update(['orderStatus' => 5]);
                $pay = Db::name('pay')->where(['orderNo' => $order['orderNo']])->field('thirdOrderNo, payMoney')->find();
                $refund = Db::name('order_refund')->where('refundId', $order['refundId'])->find();
                // $refundNo = '20' . date("YmdHis").rand(1000,9999);
                // $map = [
                //     'userId' => $order['userId'],
                //     'orderId' => $order['orderId'],
                //     'orderNo' => $order['orderNo'],
                //     'refundNo' => $refundNo,
                //     'refundMoney' => $order['payMoney'],
                //     'refundStatus' => 1,
                //     'refundReason' => '其他原因',
                //     'refundType' => 0,
                //     'createTime' => time()
                // ];
                // Db::name('order_refund')->where('')->update($map);

                $isAdd = false;
                if ($order['payType'] == 1) {
                    $alipay = new Alipay();
                    $result = $alipay->Back($order['orderNo'], $pay['thirdOrderNo'], $refund['refundNo'], $order['payMoney'], $order['orderFrom']);
                    if ($result == 1) {
                        $isAdd = true;
                    } else {
                        $this->error = '操作失败';
                        return;
                    }

                }
                if ($order['payType'] == 2) {
                    // 判断订单来源
                    if ($order['orderFrom'] == 1) {
                        $wxApi = new WxApiH5();
                    }
                    if ($order['orderFrom'] == 2 || $order['orderFrom'] == 3) {
                        $wxApi = new WxApi();
                    }

                    $result = $wxApi->refund($pay['thirdOrderNo'], $refund['refundNo'], $order['payMoney']*100, $order['payMoney']*100, $order['isMemberGoods']);
                    if ($result == 1) {
                        $isAdd = true;
                    } else {
                        $this->error = $result;
                        return;
                    }
                }

                // 退款处理成功，修改状态
                if ($isAdd) {
                    Db::name('order')->where($where)->update(['orderStatus' => 6]);
                    Db::name('order_refund')->where(['orderNo' => $order['orderNo']])->update(['refundStatus' => 2, 'resolveTime' => time()]);
                    Db::name('commission')->where($where)->update(['commissionStatus' => 3, 'isSettlement' => 0]);
                    if ($order['couponId']) {
                        Db::name('couuser')->where('cuId', $order['couponId'])->update(['cuStatus' => 2]);
                    }
                    // 判断是否首次购买会员产品，修改用户的状态
                    $orderMemberList = Db::name('order')
                        ->where([
                            'userId' => $order['userId'], 
                            'isMemberGoods' => 1
                        ])
                        ->whereIn('orderStatus', '2,3,4')
                        ->select();
                    
                    if (count($orderMemberList) == 0 && $order['isMemberGoods'] == 1) {
                        Db::name('user')->where('userId', $order['userId'])->update([
                            'isBuyMemberGoods' => 0,
                            'memberTime' => 0,
                            'auditStatus' => 0,
                            'auditTime' => 0,
                            'userType' => 1,
                        ]);
                    }
                    $commissionList = Db::name('commission')->where($where)->select();
                    foreach($commissionList as $key => $item) {
                        Db::name('user')->where('userId', $item['beneficiaryUserId'])->setDec('freezeAmount', $item['commissionMoney']);
                    }
                }
            } else {
                $this->error = '该订单，不在退款中的状态';
                return;
            }


            Db::commit();
            return '操作成功';
            
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }



}
