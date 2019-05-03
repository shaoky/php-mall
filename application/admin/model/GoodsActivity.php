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
class GoodsActivity extends Common {
    // protected $autoWriteTimestamp = true;
    // protected $createTime = 'createTime';

    public function addAd($params) {
        if (empty($params['mark'])) {
            $this->error = '请传活动标识';
            return;
        }

        try {
            $result = Db::name('GoodsActivityPosition')->where('mark', $params['mark'])->find();
            if ($result == null) {
                $this->error = '该活动不存在';
                return;
            }
           

            foreach ($params['goodsId'] as $item) {
                $arr = [
                    'goodsId' => $item,
                    'gaSort' => $params['gaSort'],
                    'gapId' => $result['gapId'],
                    'isOpen' => $params['isOpen']
                ];
                // $form[] = $arr;
                $goodsActivity = Db::name('GoodsActivity')->where([['goodsId', '=', $item], ['gapId', '=', $result['gapId']]])->find();
                if ($goodsActivity == null) {
                    Db::name('GoodsActivity')->insert($arr);
                }
            }
            // $image = Db::name('GoodsActivity')->insertAll($form);

            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getAdList($params) {
        if (empty($params['title'])) {
            $params['title'] = '';
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        $where = [];
        if (isset($params['isOpen'])) {
            $where['a.isOpen'] = $params['isOpen'];
        }
        if (isset($params['mark'])) {
            $where['b.mark'] = $params['mark'];
        }
        $data['list'] = $this->alias('a')
            ->join('goodsActivityPosition b', 'a.gapId = b.gapId')
            ->field('a.*, b.title')
            // ->whereLike('a.title', '%'.$params['title'].'%')
            ->where($where)
            ->order('gaSort desc')
            ->page($params['page'], $params['size'])
            ->select();
        // halt($data['list']);
        foreach($data['list'] as $item) {
            $item['isOpen'] = getStatusName('isOpen', $item['isOpen']);
        }
        $data['count'] = $this->where($where)->alias('a')->join('goodsActivityPosition b', 'a.gapId = b.gapId')->count();
        return $data;
    }

    public function updateAd($params) {
        try {
            $data = $this->where('gaId', $params['gaId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                // $this->error = '更新失败';
                return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteAd($params) {
        if (empty($params['gaId'])) {
            $this->error = '请选择要删除的商品';
            return false;
        }
        try {
            $data = $this->where('adId', $params['adId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '商品不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getAdInfo($params) {
        if (empty($params['gaId'])) {
            $this->error = '商品id不存在';
            return false;
        }
        try {
            $data['info'] = $this->where('adId', $params['adId'])->find();
            // echo $data['info'];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}