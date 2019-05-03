<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class AdPosition extends Validate {
	protected $rule = [
        'title' => 'require|max:50',
        'width' => 'require',
        'height' => 'require',
        'mark' => 'require'
    ];

    protected $message  =   [
        'title.require' => '请输入广告位置名称',
        'title.max'     => '广告位置不能超过50个字符',
        'width.require' => '广告位宽度',
        'height.require' => '广告位高度',
        'mark.require' => '请输入广告标记码',
    ];
}