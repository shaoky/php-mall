<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Login extends Validate {
	protected $rule = [
        'loginName' => 'require',
        'loginSms' => 'require',
        'code' => 'require',
        'userNo' => 'require',
        'loginPwd' => 'require'
    ];

    protected $message  =   [
        'loginName.require' => '请输入手机号',
        'loginSms.require' => '请输入短信验证码',
        'loginPwd.require' => '请输入密码',
    ];
    protected $scene = [
        'login' => ['loginName', 'loginSms'],
        'register' => ['loginName', 'userNo'],
        'passwordLogin' => ['loginName', 'loginPwd']
    ];
}