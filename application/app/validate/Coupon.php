<?php 
namespace app\app\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Coupon extends Validate {
	protected $rule = [
        'redeemCode' => 'require',
    ];

    protected $message  =   [
        'redeemCode.require' => '请输入兑换码',
    ];

    protected $scene = [
        'ExchangeCoupon' => ['redeemCode'],
    ];
    
}