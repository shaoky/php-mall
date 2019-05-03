<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 用户验证器
 */
class UserLevel extends Validate {
	protected $rule = [
        'levelId' => 'require',
    ];

    protected $scene = [
        'delete' => ['levelId']
    ];
}