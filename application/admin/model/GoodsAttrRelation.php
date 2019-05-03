<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */

/**
 * {post} / 2. 商品分类关联表
 * @apiName ad
 * @apiGroup adminGoodsGroup
 * @apiSuccess {Number} goodsClassId 主键
 * @apiSuccess {Number} parentId 分类父Id
 * @apiSuccess {String} goodsClassName 广告标题
 * @apiSuccess {String} imageUrl 广告图片
 * @apiSuccess {Number} order 排序
 * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
 * @apiVersion 1.0.0
 */
class GoodsAttrRelation extends Model {

    public function add($params) {
        
        try {
            $data = $this->save($params);
            return '添加成功';
        } catch (\Exception $e) {
            echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getList($params) {
        // if (empty($params['title'])) {
        //     $params['title'] = '';
        // }
        $where = [];
        
        if (isset($params['goodsClassId'])) {
            $where['goodsClassId'] = $params['goodsClassId'];
        }

        // if (isset($params['positionId'])) {
        //     $where['a.positionId'] = $params['positionId'];
        // }
        $data['list'] = $this->alias('a')
            ->join('goods_type b', 'b.goodsClassId = a.goodsClassId')
            ->join('goods_attr c', 'a.attrId = c.attrId')
            ->field('a.*, b.goodsClassName, c.attrName')
            ->order('sort desc')
            ->where($where)->select();
        // foreach($data['list'] as $key=>$item) {
        //     $data['list'][$key]['children'] = $this->where('goodsClassId', $item['goodsClassId'])->select();
        // }
        return $data;
    }

    public function updateGoodsType($params) {
        if (empty($params['goodsClassId'])) {
            $this->error = '商品分类id出错了';
            return false;
        }
        try {
            $data = $this->where('goodsClassId', $params['goodsClassId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                $this->error = '更新失败';
                // return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteGoodsType($params) {
        if (empty($params['goodsClassId'])) {
            $this->error = '请选择要删除的商品分类';
            return false;
        }

        Db::startTrans();
        try {
            $data = Db::name('goods_type')->where('goodsClassId', $params['goodsClassId'])->delete();
            // 看有没有存在下级分类
            $list = Db::name('goods_type')->where('parentId', $params['goodsClassId'])->select();
            if ($list) {
                $this->error = '该分类下存在子分类，请删除后重试';
                return; 
            }

            // 查看该级下，是否存在商品，有：拒绝删除
            $goods = Db::name('goods')->where('goodsClassId', $params['goodsClassId'])->select();
            if ($goods) {
                $this->error = '该分类下存在商品，请删除商品后重试';
                return;
            }
            Db::commit();
            if ($data == 1) {
                return '删除成功';
            } else {
                $this->error = '商品分类不存在';
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