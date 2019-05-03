<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 用户地址验证器
 */
class UserAddress extends Validate {
	protected $rule = [
        'userName' => 'require',
        'userPhone' => ['require', 'regex' => '^((13[0-9])|(14[5,7,9])|(15[^4])|(18[0-9])|(17[0,1,3,5,6,7,8]))\\d{8}$'],
        'provinceId' => 'require',
        'cityId' => 'require',
        'countyId' => 'require',
        'address' => 'require',
        'isDefault' => 'require'
    ];

    protected $message  =   [
        'userName.require' => '请输入用户姓名',
        'userPhone.require' => '请输入手机号',
        'userPhone.regex' => '请输入正确手机号',
        'provinceId.require' => '请选择省',
        'cityId.require' => '请选择市',
        'countyId.require' => '请选择区县',
        'address.require' => '请输入详细地址',
        'isDefault.require' => '请设置是否默认'
    ];
}