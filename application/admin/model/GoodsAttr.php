<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */


class GoodsAttr extends Model {

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
        $where = [];
        // if(!empty($params['isOpen'])) {
        //     $where[] = ['a.isOpen', '=', $params['isOpen']];
        // }
        // if(!empty($params['goodsClassId'])) {
        //     $where[] = ['a.goodsClassId', '=', $params['goodsClassId']];
        // }
        if(!empty($params['gbId'])) {
            $where[] = ['gbId', '=', $params['gbId']];
        }
        Db::startTrans();
        try {
            $data['list'] = Db::name('goods_attr')
            // ->alias('a')
            ->where($where)
            // ->join('goods_type b', 'a.goodsClassId = b.goodsClassId')
            // ->field('a.*, b.goodsClassName')
            // ->order('sort', 'desc')
            ->select();
            foreach($data['list'] as $key => $item) {
                $data['list'][$key]['attrTypeName'] = getStatusName('attrType', $item['attrType']);
                $data['list'][$key]['isOpen'] = getStatusName('isOpen', $item['isOpen']);
                $data['list'][$key]['isRequired'] = getStatusName('isOpen', $item['isRequired']);
            }
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }

        return $data;
    }

    public function updateGoodsAttr($params) {
        try {
            $data = $this->where('attrId', $params['attrId'])->update($params);
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

    public function deleteGoodsAttr($params) {
        try {
            $data = $this->where('attrId', $params['attrId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                $this->error = '商品属性不存在';
            }
        } catch (\Exception $e) {
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