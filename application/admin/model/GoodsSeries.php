<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsSeriesGroup admin-商品系列模块
 */


class GoodsSeries extends Model {

    public function add($params) {
        if (empty($params['goodsId'])) {
            $this->error = '请传goodsId';
            return;
        }
        try {
            $list = [];
            foreach ($params['goodsIds'] as $key => $item) {
                $list[] = [
                    'seriesSort' => 100,
                    'goodsId' => $params['goodsId'],
                    'goodsChildrenId' => $item
                ];
            }
            $data = $this->insertAll($list);
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getList($params) {
        // if (empty($params['title'])) {
        //     $params['title'] = '';
        // }
        $where = [];

        $where[] = ['b.isOpen', '=', 1];
        // if (isset($params['positionId'])) {
        //     $where['a.positionId'] = $params['positionId'];
        // }
        try {
            $data['list'] = Db::name('goods_series')->alias('a')
                ->join('goods b', 'a.goodsChildrenId = b.goodsId')
                ->field('a.*, b.*')
                ->where('a.goodsId', $params['goodsId'])
                ->order('seriesSort', 'desc')
                ->page('sort', 'desc')
                ->select();
                $data['count'] = Db::name('goods_series')->alias('a')
                ->join('goods b', 'a.goodsId = b.goodsId')
                ->field('a.*, b.*')
                ->where('a.goodsId', $params['goodsId'])->count();    
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function updateGoodsSeries($params) {
        try {
            $data = Db::name('goods_series')->where('seriesId', $params['seriesId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                // $this->error = '更新失败';
                // return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteGoodsSeries($params) {
        Db::startTrans();
        try {
            $data = Db::name('goods_series')->where('seriesId', $params['seriesId'])->delete();
            Db::commit();
            if ($data == 1) {
                return '删除成功';
            } else {
                $this->error = '删除失败';
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getGoodsTypeInfo($params) {
        if (empty($params['goodsClassId'])) {
            $this->error = '商品分类id出错了';
            return false;
        }
        try {
            $data['info'] = $this->where('goodsClassId', $params['goodsClassId'])->find();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}