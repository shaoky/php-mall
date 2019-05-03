<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;

/**
 * @apiDefine adminAdGroup admin-广告模块
 */

/**
 * @api {post} / 2. 广告表
 * @apiName ad
 * @apiGroup adminAdGroup
 * @apiSuccess {Number} adId 主键
 * @apiSuccess {String} title 广告标题
 * @apiSuccess {String} imageUrl 广告图片
 * @apiSuccess {Number} type 广告类型：1产品，2网页，3内页
 * @apiSuccess {String} operation 广告操作
 * @apiSuccess {Number} order 排序
 * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
 * @apiVersion 1.0.0
 */
class GoodsSeckill extends Common {
    // protected $autoWriteTimestamp = true;
    // protected $createTime = 'createTime';

    public function addData($params) {
        // if (empty($params['mark'])) {
        //     $this->error = '请传活动标识';
        //     return;
        // }

        try {
            $result = Db::name('GoodsSeckill')->insert($params);
            
            if ($result) {
                return '添加成功';
            } else {
                $this->error = '添加失败';
                return;
            }
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getDataList($params) {
        $where = [];
        if (isset($params['gsStatus'])) {
            switch ($params['gsStatus']) {
                case 1:
                    $where[] = ['a.startTime', '>', time()];
                    break;
                case 2:
                    $where[] = ['a.endTime', '<', time()];
                    break;
                case 3:
                    $where[] = ['a.startTime', '<', time()];
                    $where[] = ['a.endTime', '>', time()];
                    break;
                default:
                    # code...
                    break;
            }
        }
        $data['list'] = $this->alias('a')
            ->join('goods b', 'a.goodsId = b.goodsId')
            ->where($where)
            ->field('a.*, b.goodsStock, b.saleCount, b.shopPrice, b.memberPrice')
            ->order('gsSort desc')
            ->page($params['page'], $params['size'])
            ->select();
        foreach($data['list'] as &$item) {
            if (time() < $item['startTime']) {
                $item['gsStatusName'] = '未开始';
            }
            if (time() > $item['endTime']) {
                $item['gsStatusName'] = '结束';
            }
            if (time() > $item['startTime'] && time() < $item['endTime']) {
                $item['gsStatusName'] = '进行中';
            }
        }
        $data['count'] = $this->alias('a')->where($where)->join('goods b', 'a.goodsId = b.goodsId')->count();
        return $data;
    }

    public function updateData($params) {
        $form = [
            'gsTitle' => $params['gsTitle'],
            'goodsId' => $params['goodsId'],
            'gsSort' => $params['gsSort'],
            'gsImage' => $params['gsImage'],
            'startTime' => $params['startTime'],
            'endTime' => $params['startTime'],
            'minBuy' => $params['minBuy'],
            'maxBuy' => $params['maxBuy'],
            'memberMinBuy' => $params['memberMinBuy'],
            'memberMaxBuy' => $params['memberMaxBuy'],
            'isCommission' => $params['isCommission'],
            'isOpen' => $params['isOpen'],
            'goodsStock' => $params['goodsStock']
        ];
        try {
            $data = $this->where('gsId', $params['gsId'])->update($form);
            if ($data == 1) {
                return '更新成功';
            } else {
                return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteData($params) {
        if (empty($params['gsId'])) {
            $this->error = '请选择要删除的活动';
            return false;
        }
        try {
            $data = $this->where('gsId', $params['gsId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '活动不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}