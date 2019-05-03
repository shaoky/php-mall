<?php
namespace app\h5\model;
use app\h5\model\Common;
use think\Db;

/**
 * @apiDefine h5OrderRefundGroup h5-退货列表
 */

/**
 * @api {post} / 1. 订单退货表
 * @apiName h5OrderRefund
 * @apiGroup h5OrderRefundGroup
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
         * @api {post} /h5/order/refund/list 1.1 退款列表
         * @apiName h5OrderRefundList
         * @apiGroup h5OrderRefundGroup
         * @apiVersion 1.0.0
         */
        try {
            $user = $this->getUserInfo();
            $data['list'] = $this->alias('a')
                ->where('a.userId',$user['userId'])
                ->leftjoin('order b','a.orderId = b.orderId')
                ->page($request->post('page',1), $request->post('size',20))
                ->order('a.createTime','desc')
                ->field('a.refundId,a.createTime,a.refundMoney,a.refundStatus,a.refundType,a.orderId')
                ->select();
            foreach($data['list'] as $item) {
                $item['goodsList'] = db('orderGoods')->where(['orderId' => $item['orderId']])->select();
                $item['refundStatusName'] = getStatusName('refundStatus',$item['refundStatus']);
                $item['refundTypeName'] = getStatusName('refundType',$item['refundType']);
            }
            $data['count'] = $this->where('userId',$user['userId'])->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

    }

    /**
     * @api {post} /h5/order/refund/add 1.2 添加退款
     * @apiName h5OrderRefundAdd
     * @apiGroup h5OrderRefundGroup
     * @apiParam {Number} orderId  订单Id
     * @apiParam {String} refundReason  退款理由：1拍错了,2不想买了,3商品无货,4其他原因
     * @apiParam {Number} refundType 退款类型：1仅退款,2退货退款
     * @apiParam {Number} [refundRemark] 退款说明
     * @apiParam {String} [refundImage] 退款图片
     * @apiVersion 1.0.0
     */

    public function OrderRefundAdd($request)
    {
        $user = $this->getUserInfo();

        $request->refundNo = '20' . date("YmdHis").rand(1000,9999);
        $order = Db::table('tp_order')->where('orderId',$request->orderId)->find();
        if ($order) {
            try {
                $refund = Db::name('order_refund')->where('orderId', $request->orderId)->select();
                if ($refund) {
                    foreach ($refund as $key => $item) {
                        if ($item['refundStatus'] == 1) { // 查看该订单，是否已经在申请退款中
                            $this->error = '已经申请过退款，请勿再次申请';
                            return;
                        }
                    }
                    
                }

                $request->refundMoney = $order['payableMoney'];
                $request->refundStatus = 1;
                $request->createTime = time();
                $request->userId = $user['userId'];

                $refundId = $this->insertGetId([
                    'refundNo' => $request->refundNo,
                    'refundMoney' => $order['payableMoney'],
                    'refundStatus' => 1,
                    'orderStatus' => $order['orderStatus'],
                    'createTime' => time(),
                    'userId' => $user['userId'],
                    'orderNo' => $order['orderNo'],
                    'orderId' => $request->orderId,
                    'refundType' => $request->refundType,
                    'refundReason' => $request->refundReason,
                    'refundImage' => $request->refundImage,
                    'refundRemark' => $request->refundRemark,
                ]);

                Db::table('tp_order')->where('orderId',$request->orderId)->update(['orderStatus'=>5, 'refundId' => $refundId]);
                if ($refundId)
                    return '申请成功';
                else
                    return '申请失败';
            }catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }
        else
        {
            $this->error = '不存在该订单';
        }
    }

    /**
     * @api {post} /h5/order/refund/info 1.3 退款信息
     * @apiName  h5OrderRefundInfo
     * @apiGroup h5OrderRefundGroup
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

            $data['info'] = $this->where('refundId',$request->refundId)
                ->find();

            if (empty($data['info'])){
                return $this->error = '没有查找到对应的退款记录';
            }

            $data['info']['refundStatusName'] = getStatusName('refundStatus',$data['info']['refundStatus']);
            $data['info']['refundTypeName'] = getStatusName('refundType',$data['info']['refundType']);
            $data['info']['goodsList'] = db('orderGoods')->where(['orderId' => $data['info']['orderId']])->select();

        }catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }

        return $data;

    }



}
