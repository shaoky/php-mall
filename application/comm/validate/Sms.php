<?php 
namespace app\comm\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Sms extends Validate {
	protected $rule = [
        'phone' => ['require', 'regex' => '^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$'],
        'type' => 'require'
    ];

    protected $message  =   [
        'phone.require' => '请输入手机号',
        'phone.regex' => '请输入正确的手机号',
        'type.require' => '请设置短信类型',
    ];
}