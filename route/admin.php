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

// Route::get('think', function () {
    // return 'hello,ThinkPHP5.1!';
// });

// Route::get('/hello', 'index/Index/hello');


Route::group('admin', [
    /**
     * 通用模块
     */
    '/common/image/add' => 'admin/common.Image/add',
    '/common/image/delete' => 'admin/common.Image/delete',
    '/common/image/list' => 'admin/common.Image/list',
    '/common/excel/withdrawal' => 'admin/common.Excel/withdrawal',
    '/common/excel/order' => 'admin/common.Excel/order',
    '/common/excel/shop' => 'admin/common.Excel/shop',

    /**
     * 主页
     */
    '/index' => 'admin/statistics.Index/index',
    '/relationMap' => 'admin/statistics.Index/relationMap',
    /**
     * 用户模块
     */
    '/register' => 'admin/Base/register', // 注册
    '/login' => 'admin/Base/login', // 登陆
    /**
     * 广告模块
     */
    // 广告列表
    '/ad/add' => 'admin/ad.Index/add', // 新增
    '/ad/info' => 'admin/ad.Index/info', // 详情
    '/ad/list' => 'admin/ad.Index/list', // 列表
    '/ad/update' => 'admin/ad.Index/update', // 更新
    '/ad/delete' => 'admin/ad.Index/delete', // 删除
    // 广告分类
    '/ad/position/add' => 'admin/ad.Position/add', // 新增
    '/ad/position/list' => 'admin/ad.Position/list', // 列表
    '/ad/position/update' => 'admin/ad.Position/update', // 更新
    '/ad/position/delete' => 'admin/ad.Position/delete', // 删除
    /**
     * 商品模块
     */
    '/goods/add' => 'admin/goods.Index/add', // 新增
    '/goods/list' => 'admin/goods.Index/list', // 列表
    '/goods/update' => 'admin/goods.Index/update', // 更新
    '/goods/delete' => 'admin/goods.Index/delete', // 删除
    '/goods/isOpen/set' => 'admin/goods.Index/setIsOpen', // 设置上下架
    '/goods/info' => 'admin/goods.Index/info', // 详情
    '/goods/image/add' => 'admin/goods.Index/addImage', // 图片添加
    '/goods/image/delete' => 'admin/goods.Index/deleteImage', // 图片删除
    '/goods/attrList' => 'admin/goods.Index/attrList', // 商品属性
    /**
     * 商品分类
     */
    '/goods/type/add' => 'admin/goods.Type/add', // 新增
    '/goods/type/list' => 'admin/goods.Type/list', // 列表
    '/goods/type/update' => 'admin/goods.Type/update', // 更新
    '/goods/type/delete' => 'admin/goods.Type/delete', // 删除
    '/goods/type/basis/info' => 'admin/goods.Type/info', // 删除
    /**
     * 商品分类关联
     */
    '/goods/attrRelation/add' => 'admin/goods.AttrRelation/add', // 新增
    '/goods/attrRelation/list' => 'admin/goods.AttrRelation/list', // 列表
    '/goods/attrRelation/update' => 'admin/goods.AttrRelation/update', // 更新
    '/goods/attrRelation/delete' => 'admin/goods.AttrRelation/delete', // 删除
    /**
     * 商品系列关联
     */
    '/goods/series/add' => 'admin/goods.Series/add', // 新增
    '/goods/series/list' => 'admin/goods.Series/list', // 列表
    '/goods/series/update' => 'admin/goods.Series/update', // 更新
    '/goods/series/delete' => 'admin/goods.Series/delete', // 删除
    /**
     * 商品属性
     */
    '/goods/attr/add' => 'admin/goods.Attr/add', // 新增
    '/goods/attr/list' => 'admin/goods.Attr/list', // 列表
    '/goods/attr/update' => 'admin/goods.Attr/update', // 更新
    '/goods/attr/delete' => 'admin/goods.Attr/delete', // 删除
    /**
     * 商品属性
     */
    '/goods/brand/add' => 'admin/goods.Brand/add', // 新增
    '/goods/brand/list' => 'admin/goods.Brand/list', // 列表
    '/goods/brand/update' => 'admin/goods.Brand/update', // 更新
    '/goods/brand/delete' => 'admin/goods.Brand/delete', // 删除
    /**
     * 商品活动模块
     */
    // 商品活动列表
    '/goods/activity/add' => 'admin/goods.Activity/add', // 新增
    '/goods/activity/info' => 'admin/goods.Activity/info', // 详情
    '/goods/activity/list' => 'admin/goods.Activity/list', // 列表
    '/goods/activity/update' => 'admin/goods.Activity/update', // 更新
    '/goods/activity/delete' => 'admin/goods.Activity/delete', // 删除
    // 商品活动位置
    '/goods/activity/position/add' => 'admin/goods.ActivityPosition/add', // 新增
    '/goods/activity/position/list' => 'admin/goods.ActivityPosition/list', // 列表
    '/goods/activity/position/update' => 'admin/goods.ActivityPosition/update', // 更新
    '/goods/activity/position/delete' => 'admin/goods.ActivityPosition/delete', // 删除
    // 商品秒杀活动
    '/goods/seckill/add' => 'admin/goods.Seckill/add', // 新增
    '/goods/seckill/list' => 'admin/goods.Seckill/list', // 列表
    '/goods/seckill/update' => 'admin/goods.Seckill/update', // 更新
    '/goods/seckill/delete' => 'admin/goods.Seckill/delete', // 删除
    // 商铺管理
    '/shop/add' => 'admin/shop.Index/add', // 新增
    '/shop/list' => 'admin/shop.Index/list', // 列表
    '/shop/update' => 'admin/shop.Index/update', // 更新
    '/shop/isOpen/set' => 'admin/shop.Index/setIsopen', 
    '/shop/auditStatus/set' => 'admin/shop.Index/setAuditStatus', 
    '/shop/discount/update' => 'admin/shop.Index/updateDiscount', // 更新
    '/shop/info' => 'admin/shop.Index/info', // 删除
    '/shop/makeqr' => 'admin/shop.Index/makeQr',
    // 店铺流水
    '/shop/statistics/list' => 'admin/shop.Statistics/list', // 列表
    // 会员优惠率
    '/shop/userLevel/update' => 'admin/shop.UserLevel/update',
    /**
     * 用户管理
     */
    '/user/list' => 'admin/user.Index/list', // 列表
    '/user/member/list' => 'admin/user.Index/memberList', // 会员列表
    '/user/member/audit' => 'admin/user.Index/memberAudit', // 会员审核
    '/user/member/info' => 'admin/user.Index/memberInfo', // 会员详情
    '/user/member/childrenInfo' => 'admin/user.Index/childrenMemberInfo', // 会员下级详情
    // 会员等级
    '/user/level/list' => 'admin/user.Level/list', // 会员等级列表
    '/user/level/add' => 'admin/user.Level/add', // 会员等级新增
    '/user/level/update' => 'admin/user.Level/update', // 会员等级更新
    '/user/level/delete' => 'admin/user.Level/delete', // 会员等级删除
    '/user/level/info' => 'admin/user.Level/info', // 会员等级删除
    /**
     * 订单管理
     */
    '/order/list' => 'admin/order.Index/list', // 订单列表
    '/order/info' => 'admin/order.Index/info', // 订单详情
    '/order/delivery' => 'admin/order.Index/delivery', //订单发货
    '/order/cancel' => 'admin/order.Index/cancel',  // 订单取消
    '/order/refund/set' => 'admin/order.OrderRefund/refund',  // 退款
    '/order/refund/list' => 'admin/order.OrderRefund/list',//退款列表
    '/order/refund/update' => 'admin/order.OrderRefund/update',//退款更新
    '/order/refund/info' => 'admin/order.OrderRefund/info',//退款详细
    /**
     * 财务管理
     */
    '/user/withdrawal/list' => 'admin/userwithdrawal.Index/list',//提现列表
    '/user/withdrawal/info' => 'admin/userwithdrawal.Index/info',//提现详细
    '/user/withdrawal/update' => 'admin/userwithdrawal.Index/update',//提现跟新
    '/user/finance/list' => 'admin/userwithdrawal.Index/userList',//用户财务
    /**
     * 数据报表
     */
    '/statistics/goods' => 'admin/statistics.Index/goodsList',//产品销售排行
    '/statistics/orders' => 'admin/statistics.Index/ordersList',//平台流水
    '/statistics/ordersExcel' => 'admin/statistics.Index/ordersListExcel', //平台流水excel
    '/statistics/transaction/profile' => 'admin/statistics.Index/TransactionProfile',//
    '/statistics/comprehensive/overview' => 'admin/statistics.Index/ComprehensiveOverview',//
    /**
     * 配置管理
     */
    '/setting/site/info' => 'admin/setting.Index/site',//网站配置
    '/setting/site/update' => 'admin/setting.Index/siteUpdate',//网站配置修改
    /**
     * Poi店铺管理
     */
    '/system/store/add' => 'admin/system.Store/add',
    '/system/store/list' => 'admin/system.Store/list',
    '/system/store/update' => 'admin/system.Store/update',
    '/system/store/delete' => 'admin/system.Store/delete',
    /**
     * 日志
     */
    '/system/log/list' => 'admin/system.Log/list',
    '/log/getdir' => 'admin/log.Index/getDir',
    '/log/getlog' => 'admin/log.Index/getLog',
    /**
     * 定时任务
     */
    '/crontab/order/settlement' => 'admin/crontab.Index/orderSettlement', // 订单结算
    '/crontab/order/confirm' => 'admin/crontab.Index/orderAutoConfirm', // 订单自动确认收货
    '/crontab/order/close' => 'admin/crontab.Index/orderClose', // 订单取消
    '/crontab/order/goldMemberCount' => 'admin/crontab.Index/goldMemberCount', // 订单取消
    /**
     * 权限
     */
    '/auth/column/list' => 'admin/auth.Index/getList', // 栏目
    '/auth/column/add' => 'admin/auth.Index/addColumn',
    '/auth/column/delete' => 'admin/auth.Index/deleteColumn',
    '/auth/column/update' => 'admin/auth.Index/updateColumn',
    '/auth/add' => 'admin/auth.Index/addAuth',
    '/auth/adduser' => 'admin/auth.Index/addUserAuth',
    '/auth/column/adduser' => 'admin/auth.Index/addColumnUserAuth',
    '/auth/column/user' => 'admin/auth.Index/getUserColumn',
    '/auth/column/getuser' => 'admin/auth.Index/getUserColumnList',
    /**
     * 优惠券
     */
    '/coupon/index' => 'admin/coupon.Index/Index', //优惠券列表
    '/coupon/add' => 'admin/coupon.Index/addCoupon', //
    '/coupon/update' => 'admin/coupon.Index/updateCoupon', //
    '/coupon/getattrgoods' => 'admin/coupon.Index/getAttrGoods', //
    '/coupon/delete' => 'admin/coupon.Index/deleteCoupon', //
    '/coupon/update/state' => 'admin/coupon.Index/updateState', //
    '/coupon/count/list' => 'admin/coupon.Index/couponCount',//
    /**
     * 优惠券活动
     */
    '/coupon/active/index' => 'admin/coupon.Active/Index',//优惠券活动列表
    '/coupon/active/add' => 'admin/coupon.Active/addCouponActive',//
    '/coupon/active/update' => 'admin/coupon.Active/updateCouponActive',//
    '/coupon/active/status' => 'admin/coupon.Active/updateStatusActive',//
    '/coupon/active/delete' => 'admin/coupon.Active/deleteActive',//
    '/coupon/active/getredeem' => 'admin/coupon.Active/getRedeem',//
    
    /**
     * 管理员
     */
    '/user/admin/list' => 'admin/user.Admin/getAdminList',//列表
    '/user/admin/update' => 'admin/user.Admin/updateAdmin',//编辑
    '/user/admin/add' => 'admin/user.Admin/addAdmin',
    '/user/admin/delete' => 'admin/user.Admin/deleteAdmin',
    '/user/admin/info' => 'admin/user.Admin/infoAdmin',
    '/user/admin/password' => 'admin/user.Admin/passwordAdmin',
    '/user/admin/passwordReset' => 'admin/user.Admin/passwordReset',
    /**
     * 分组
     */
    '/group/list'=> 'admin/group.Index/getGroupList',
    '/group/add'=> 'admin/group.Index/addGroup',
    '/group/update'=> 'admin/group.Index/updateGroup',
    '/group/delete'=> 'admin/group.Index/deleteGroup',
    /**
     * 软件管理
     */
    '/software/add' => 'admin/system.software/add',
    '/software/list' => 'admin/system.software/list',
    '/software/update' => 'admin/system.software/update',
    '/software/delete' => 'admin/system.software/delete',
    /**
     * 版本管理
     */
    '/version/add' => 'admin/system.version/add',
    '/version/list' => 'admin/system.version/list',
    '/version/update' => 'admin/system.version/update',
    '/version/delete' => 'admin/system.version/delete',
    /**
     * 系统日志
     */
    '/log/login/list' => 'admin/system.log/loginList', // 后台登陆日志
    '/log/operation/list' => 'admin/system.log/operationList', // 后台操作日志
    /**
     *********************数据管理 **************************
     */
    /**
     * 商品类型
     */
    '/goods/basis/add' => 'admin/goods.Basis/add',
    '/goods/basis/delete' => 'admin/goods.Basis/delete',
    '/goods/basis/update' => 'admin/goods.Basis/update',
    '/goods/basis/list' => 'admin/goods.Basis/list',
    '/goods/basis/info' => 'admin/goods.Basis/info',

    /**
     * 商品规格
     */
    '/goods/spec/add' => 'admin/goods.Spec/add',
    '/goods/spec/delete' => 'admin/goods.Spec/delete',
    '/goods/spec/update' => 'admin/goods.Spec/update',
    '/goods/spec/list' => 'admin/goods.Spec/list',
    '/goods/spec/info' => 'admin/goods.Spec/info',

    /**
     * 类目管理
     */
    'goods/typeApp/list' => 'admin/goods.TypeApp/list',
    'goods/typeApp/add' => 'admin/goods.TypeApp/add',
    'goods/typeApp/update' => 'admin/goods.TypeApp/update',
    'goods/typeApp/delete' => 'admin/goods.TypeApp/delete',

])
->header([
    'Access-Control-Allow-Origin' => '*',
    'Content-Type' => 'application/json;charset=UTF-8'
])
->allowCrossDomain();




/**
 * �û�ģ��
 */

// Route::post('/admin/register', 'admin/user.Admin/register'); // ע��
// Route::post('/admin/login', 'admin/user.Admin/login'); // ��½

/**
 * ���ģ��
 */
// Route::post('/admin/ad/add', 'admin/ad.Index/add'); // ����
// Route::post('/admin/ad/list', 'admin/ad.Index/list'); // �б�

// Route::post('/admin/common/image/add', 'admin/common.Image/add'); // ͼƬ�ϴ�

return [

];
