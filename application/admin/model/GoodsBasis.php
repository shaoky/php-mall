<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */

class GoodsBasis extends Model {

    public function add($params) {
        try {
            $data = Db::name('goods_basis')->insert($params);
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getList($params) {
        $data['list'] = Db::name('goods_basis')->page($params['page'], $params['size'])->select();
        foreach($data['list'] as &$item) {
            $item['specNum'] = Db::name('goods_attr')->where('gbId', $item['gbId'])->count();
            $item['attrNum'] = Db::name('goods_spec')->where('gbId', $item['gbId'])->count();
        }
        $data['count'] = Db::name('goods_basis')->count();
        // foreach($data['list'] as $key=>$item) {
        //     $data['list'][$key]['isOpen'] = getStatusName('isOpen', $item['isOpen']);
        //     $where1['parentId'] = $item['goodsClassId'];
        //     if (isset($params['isOpen'])) {
        //         $where1['isOpen'] = $params['isOpen'];
        //     }
        //     $data['list'][$key]['children'] = $this->where($where1)->select();
        //     foreach($data['list'][$key]['children'] as $key1 => $item1) {
        //         $data['list'][$key]['children'][$key1]['isOpen'] = getStatusName('isOpen', $item1['isOpen']);
        //     }
        // }
        return $data;
    }


    public function updateGoodsBasis($params) {
        try {
            $data = Db::name('goods_basis')->where('gbId', $params['gbId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                $this->error = '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteGoodsBasis($params) {
        if (empty($params['gbId'])) {
            $this->error = '请选择要删除的类型';
            return false;
        }

        Db::startTrans();
        try {
            $data = Db::name('goods_basis')->where('gbId', $params['gbId'])->delete();
            // $data = Db::name('goods_type')->where('goodsClassId', $params['goodsClassId'])->delete();
            // // 看有没有存在下级分类
            // $list = Db::name('goods_type')->where('parentId', $params['goodsClassId'])->select();
            // if ($list) {
            //     $this->error = '该分类下存在子分类，请删除后重试';
            //     return; 
            // }

            // // 查看该级下，是否存在商品，有：拒绝删除
            // $goods = Db::name('goods')->where('goodsClassId', $params['goodsClassId'])->select();
            // if ($goods) {
            //     $this->error = '该分类下存在商品，请删除商品后重试';
            //     return;
            // }
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

    public function getGoodsBasisInfo($params) {
        if (empty($params['gbId'])) {
            $this->error = '类型id出错了';
            return false;
        }
        try {
            $data['info'] = Db::name('goods_basis')->where('gbId', $params['gbId'])->find();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}