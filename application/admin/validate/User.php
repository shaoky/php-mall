<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 用户验证器
 */
class User extends Validate {
	protected $rule = [
        'userId' => 'require',
        'auditStatus' => 'require'
    ];

    protected $scene = [
        'audit' => ['userId', 'auditStatus'],
        'info' => ['userId']
    ];
}