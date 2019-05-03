<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;

/**
 * @apiDefine adminAdGroup admin-店铺模块
 */

class ShopStatistics extends Common {

    public function getList($params) {
        try {
            $where = [];
            if (!empty($params['shopName'])) {
                $where[] = ['b.shopName', 'like', '%'.$params['shopName'].'%'];
            }
            if (!empty($params['begintime'])) {
                $where[] = ['a.paymentTime', '>=' , $params['begintime']];
                $where[] = ['a.paymentTime', '<=' , $params['endtime']];
            }
            $where[] = ['a.orderStatus', '=', 4];
            $data['list'] = Db::name('shop_order')->alias('a')->join('shop b', 'a.shopId = b.shopId')
            ->field('a.*, b.shopName')    
            ->where($where)->select();

            // 统计
            $data['count'] =  Db::name('shop_order')->alias('a')->join('shop b', 'a.shopId = b.shopId')
            ->field('a.*, b.shopName')    
            ->where($where)->count();
            $data['all'] = Db::name('shop_order')->where(['orderStatus' => 4])->field('SUM(payMoney) as payMoney, SUM(shopMoney) as shopMoney,SUM(platformMoney) as platformMoney,SUM(commissionMoney) as commissionMoney')->find();

            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

}