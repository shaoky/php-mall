<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 商品验证器
 */
class OrderRefund extends Validate {
	protected $rule = [
        'orderId' => 'require',
        'refundReason' => 'require',
        'refundType' => 'require',
        'refundRemark' => 'max:255',
    ];

    protected $message  =   [
        'orderId.require' => '订单Id为空',
        'refundReason.require' => '请填写退款理由',
        'refundType.require' => '请填写退款类型',
        'refundRemark.max' => '退款理由最多255个字',
    ];

}