<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Order extends Validate {
	protected $rule = [
        'goodsId' => 'require',
        'goodsNum' => 'require',
        'addressId' => 'require',
        'goodsList' => 'require',
        'orderId' => 'require',
        'orderNo' => 'require',
        'payType' => 'require'
    ];

    protected $message  =   [
        'addressId.require' => '请添加默认地址',
        'payType.require' => '请选择支付方式'
    ];

    protected $scene = [
        'goodsOrder' => ['goodsNum'],
        'goodsPreview' => ['goodsId', 'goodsNum'],
        'add' => ['addressId', 'goodsList', 'payType'],
        'cancel' => ['orderId'],
        'confirm' => ['orderId'],
        'info' => ['orderId'],
        'pay' => ['orderNo'],
        'delete' => ['orderId'],
        'settlement' => ['orderNo'],
        'selectOrder' => ['orderNo']
    ];
}