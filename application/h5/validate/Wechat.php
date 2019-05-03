<?php 
namespace app\h5\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Wechat extends Validate {
	protected $rule = [
        'url' => 'require',
    ];

    protected $message  =   [
    ];

    protected $scene = [
        'signature' => ['url'],
    ];
    
}