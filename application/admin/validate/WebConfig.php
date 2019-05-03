<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class WebConfig extends Validate {
	protected $rule = [
        // 'servicePhone'  =>['require', 'max' => 25, 'regex' => '^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$'],
        'servicePhone' => 'require',
        'freeShippingMoney' => 'require',
        'autoConfirmDelivery' => 'require',
        'deliverMoney' => 'require',
        'withdrawalCycle' => 'require|number|between:1,31',
        'orderSettlementCycle' => 'require',
    ];

    protected $message  =   [
        'servicePhone.require' => '请输入手机号',
        // 'servicePhone.regex' => '这不是手机号',
        'freeShippingMoney.require' => '请输入满多少免配送费',
        'autoConfirmDelivery.require' => '请输入发货后自动确认收货',
        'deliverMoney.require' => '请输入配送费',
        'withdrawalCycle.require' => '请输入提现周期每月',
        'withdrawalCycle.max' => '提现周期每月不能大于31号或小于1号',
        'orderSettlementCycle.require' => '请输入订单结算周期',
    ];
}