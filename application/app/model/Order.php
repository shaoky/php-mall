<?php
namespace app\app\model;
use think\Db;
use think\Model;
use app\h5\model\Common;

/**
 * @apiDefine appGroup app-买手app
 */

class Order extends Common {

    public function cartPreview($params) {
        $user = $this->getUserInfo();
        
        $where = [
            'userId' => $user['userId'],
            'isSelected' => 1,
            'b.isOpen' => 1
        ];
        Db::startTrans();
        try {
            $config = Db::name('web_config')->find();
            $data['goodsList'] = Db::name('cart')->alias('a')
                ->join('goods b', 'a.goodsId = b.goodsId')
                ->field('a.goodsNum, b.*')
                ->where($where)
                ->select();
            if (!$data['goodsList']) {
                $this->error = '请添加购物车商品';
                return;
            }
            $data['totalMoney'] = 0;
            $isFreeShipping = 0;
            foreach($data['goodsList'] as $key=>$item) {
                if ($item['isFreeShipping'] == 1) {
                    $isFreeShipping = 1;
                }
                if ($user['userType'] == 1) {
                    $data['totalMoney'] += $item['shopPrice'] * $item['goodsNum'];
                    $data['goodsList'][$key]['goodsPrice'] = $item['shopPrice'];
                } else {
                    $data['totalMoney'] += $item['memberPrice'] * $item['goodsNum'];
                    $data['goodsList'][$key]['goodsPrice'] = $item['memberPrice'];
                }
            }

            if ($isFreeShipping == 1) {
                $data['deliverMoney'] = 0;
                $data['payMoney'] = $data['totalMoney'];
            } else {
                if ($data['totalMoney'] < $config['deliverMoney']) {
                    $data['deliverMoney'] = $config['deliverMoney'];
                    $data['payMoney'] = $data['totalMoney'] +  $data['deliverMoney'];
                } else {
                    $data['deliverMoney'] = 0;
                    $data['payMoney'] = $data['totalMoney'];
                }
            }
           
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
}
