<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class GoodsAttr extends Validate {
	protected $rule = [
        'attrId' => 'require',
        'attrName' => 'require',
        'fieldName' => 'require',
        'attrType' => 'require',
        'sort' => 'require',
        'isOpen' => 'require'
    ];

    protected $scene = [
        'add' => ['attrName', 'attrType', 'sort', 'isOpen'],
        'update' => ['attrId'],
        'delete' => ['attrId']
    ];

}