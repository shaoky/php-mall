<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Ad extends Validate {
	protected $rule = [
        'title' => 'require|max:50',
        'positionId' => 'require',
        'type' => 'require',
        'sort' => 'require|max:5',
        'imageUrl' => 'require'
    ];

    protected $message  =   [
        'title.require' => '请输入广告名称',
        'title.max'     => '广告名称不能超过50个字符',
        'positionId.require' => '请选择广告位置',
        'type.require' => '请选择广告类型',
        'sort.require' => '请输入广告排序',
        'imageUrl.require' => '请添加广告图片'
    ];
}