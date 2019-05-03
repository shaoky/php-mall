<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class GoodsActivityPosition extends Validate {
	protected $rule = [
        'title' => 'require|max:50',
        // 'width' => 'require',
        // 'height' => 'require',
        'mark' => 'require'
    ];

    protected $message  =   [
        'title.require' => '请输入活动名称',
        'title.max'     => '活动不能超过50个字符',
        // 'width.require' => '广告位宽度',
        // 'height.require' => '广告位高度',
        'mark.require' => '请输入活动标记码',
    ];
}