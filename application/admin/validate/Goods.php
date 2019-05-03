<?php 
namespace app\admin\validate;
use think\Validate;
/**
 * 商品验证器
 */
class Goods extends Validate {
	protected $rule = [
        'goodsClassId' => 'require',
        'goodsName' => 'require|max:100',
        'goodsBrandId' => 'require',
        'marketPrice' => 'require',
        'shopPrice' => 'require',
        'goodsStock' => 'require',
        'goodsImage' => 'require',
        'goodsBannerList' => 'require',
        'goodsDetailList' => 'require',
        'sort' => 'require'
        // 'goodsAttrUnit' => 'require',
        // 'goodsAttrNumber' => 'require'
    ];

    protected $message  =   [
        'goodsClassId.require' => '请输入商品分类',
        'goodsName.require' => '请输入商品名称',
        'goodsName.max'     => '商品名称不能超过100个字符',
        'goodsBrandId.require' => '请选择品牌',
        'marketPrice' => '请输入市场价',
        'shopPrice' => '请输入平台价',
        'goodsStock' => '请输入商品库存',
        'goodsImage' => '请添加商品图片',
        'goodsBannerList' => '请添加轮播图',
        'goodsDetailList' => '请添加详情图',
    ];
}