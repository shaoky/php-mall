<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class GoodsType extends Validate {
	protected $rule = [
        'goodsClassName' => 'require',
        'sort' => 'require',
        'isOpen' => 'require'
    ];

    protected $message  =   [
        'goodsClassName.require' => '请输入分类名称',
        'sort.require' => '请输入排序',
        'isOpen.require' => '请选择是否启用'
    ];
}