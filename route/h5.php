<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// Route::post('/common/image/add', 'common/Image/add');
Route::group('h5', [
    '/login' => 'h5/Login/index',
    '/register' => 'h5/Login/register',
    '/referee/list' => 'h5/Login/referee',
    '/referee/info' => 'h5/Login/refereeInfo',
    '/autoLogin' => 'h5/Login/autoLogin',
    /**
     * 微信模块
     */
    '/wechat/signature' => 'h5/Wechat/signature',
    '/wechat/oauth' => 'h5/Wechat/oauth',
    /**
     * 首页接口
     */
    'index/data' => 'h5/Index/getData', //
    'index/goods' => 'h5/Index/goods', //
    'index/store' => 'h5/Index/store', //
    'index/share' => 'h5/Index/share', //
    /**
     * 商品模块
     */
    'goods/list' => 'h5/Goods/list',
    'goods/member/list' => 'h5/Goods/memberList',
    'goods/info' => 'h5/Goods/info',
    'goods/series/list' => 'h5/Goods/seriesList',
    'goods/type/list' => 'h5/Goods/goodsTypeList',
    'goods/search/list' => 'h5/Goods/goodsSearchList',
    /**
     * 购物车模块
     */
    'cart/list' => 'h5/Cart/list',
    'cart/add' => 'h5/Cart/add',
    'cart/update' => 'h5/Cart/update',
    'cart/delete' => 'h5/Cart/delete',
    'cart/select' => 'h5/Cart/select',
    /**
     * 订单模块
     */
    'order/goodsPreview' => 'h5/Order/goodsPreview', // 单个商品购买
    'order/cartPreview' => 'h5/Order/cartPreview', // 购物车下单
    'order/cartValidate' => 'h5/Order/cartValidate', // 验证是否可以从购物车，加入订单预览
    'order/list' => 'h5/Order/list',
    'order/info' => 'h5/Order/info',
    'order/add' => 'h5/Order/add',
    'order/pay' => 'h5/Pay/pay',
    'order/cancel' => 'h5/Order/cancelOrder',
    'order/confirm' => 'h5/Order/confirmOrder',
    'order/delete' => 'h5/Order/delete',
    /**
     * 订单支付
     */
    'wxpay/settlement' => 'h5/WxPay/wxPay',
    'wxpay/success' => 'h5/WxPay/paysuccess', // 回调页面
    'wxpay/shopSettlement' => 'h5/WxPay/shopWxPay',
    'wxpay/shopSuccess' => 'h5/WxPay/shopPaysuccess',
    'wxpay/selectOrder' => 'h5/WxPay/selectOrder',
    /**
     * 退货模块
     */
    'order/refund/list' => 'h5/OrderRefund/list',
    'order/refund/add' => 'h5/OrderRefund/add',
    'order/refund/info' => 'h5/OrderRefund/info',
    /**
     * 个人信息
     */
    'user/info' => 'h5/User/info',
    'user/update' => 'h5/User/update',
    'user/index' => 'h5/User/index',
    'user/logout' => 'h5/User/logout',
    /**
     * 用户地址
     */
    'user/address/add' => 'h5/UserAddress/add',
    'user/address/list' => 'h5/UserAddress/list',
    'user/address/update' => 'h5/UserAddress/update',
    'user/address/delete' => 'h5/UserAddress/delete',
    'user/address/default/set' => 'h5/UserAddress/setDefault',
    'user/address/default/get' => 'h5/UserAddress/getDefault',
    'user/address/info' => 'h5/UserAddress/getInfo',
    'commission' => 'h5/Order/commission',
])
->header([
    'Access-Control-Allow-Origin' => '*',
    'Content-Type' => 'application/json;charset=UTF-8'
])
->allowCrossDomain();


return [

];
