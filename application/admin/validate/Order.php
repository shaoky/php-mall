<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 订单验证器
 */
class Order extends Validate {
	protected $rule = [
        'orderId' => 'require',
        'courierName' => 'require',
        'courierNo' => 'require'
    ];

    protected $scene = [
        'info' => ['orderId'],
        'delivery' => ['orderId', 'courierNo', 'courierName'],
        'cancel' => ['orderId'],
        'refund' => ['orderId']
    ];
}