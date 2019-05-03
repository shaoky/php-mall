<?php
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class UserWithdrawal extends Validate {
	protected $rule = [
        'status' => ['require','regex'=>'^[123]$'],
        'withdrawalId' => 'require',
        // 'imageUrl' => 'require'
    ];

    protected $message  =   [
        'status.regex' => 'status数值错误',
        'status.require' => 'status数值为空',
        'withdrawalId.require' => '没有提现Id',
        // 'imageUrl.require' => '请上传图片'
    ];
}
