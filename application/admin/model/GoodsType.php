<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */

class GoodsType extends Model {

    public function add($params) {
        if (empty($params['classId'])) {
            $params['parentId'] = 0;
        } else {
            $params['parentId'] = $params['classId'];
        }
        try {
            $data = $this->save($params);
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
        
        if (isset($params['goodsClassId'])) {
            $where['parentId'] = $params['goodsClassId'];
        } else {
            // $where['parentId'] = 0;
        }

        if (isset($params['isOpen'])) {
            $where['isOpen'] = $params['isOpen'];
        }
        // if (isset($params['positionId'])) {
        //     $where['a.positionId'] = $params['positionId'];
        // }
        $result = Db::name('goods_type')->alias('a')
            ->leftJoin('goods_basis b', 'a.gbId = b.gbId')
            ->field('a.*, b.*')
            ->where($where)->order('sort', 'desc')->select();
        
        if ($params['type'] == 1) {
            $data['list'] = $this->getTree($result);
        }
        if ($params['type'] == 2) {
            $data['list'] = $this->getTree1($result);
        }
        
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

    private function getTree($array, $pid = 0, $level = 0){
        global $tree;
        foreach($array as $key => $value) {
            if($value['parentId'] == $pid) {
                $value['level'] = $level;
                $tree[] = $value;
                $this->getTree($array , $value['goodsClassId'] ,$level+1);
            }
        }
        return $tree;
    }

    public function getTree1($array, $pid = 0, $level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        $list = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parentId'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                $value['isOpen'] = getStatusName('isOpen', $value['isOpen']);
                //把数组放到list中
                $value['children'] = $this->getTree1($array, $value['goodsClassId'], $level+1);
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                // unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                

            }
        }
        return $list;
    }

    public function updateGoodsType($params) {
        if (empty($params['goodsClassId'])) {
            $this->error = '商品分类id出错了';
            return false;
        }
        if (empty($params['gbId'])) {
            $params['gbId'] = null;
        }
        $form = [
            'goodsClassName' => $params['goodsClassName'],
            'imageUrl' => $params['imageUrl'],
            'isOpen' => $params['isOpen'],
            'parentId' => $params['parentId'],
            'gbId' => $params['gbId'],
            'sort' => $params['sort']
        ];
        try {
            $data = $this->where('goodsClassId', $params['goodsClassId'])->update($form);
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
            $goodsType = Db::name('goods_type')->where('goodsClassId', $params['goodsClassId'])->find();
            if ($goodsType['gbId']) {
                $where = [
                    'gbId' => $goodsType['gbId'],
                    'isOpen' => 1
                ];
                $data['goodsAttr'] = Db::name('goods_attr')->where($where)->order('sort', 'desc')->select();
                $data['goodsSpec'] = Db::name('goods_spec')->where($where)->order('specSort', 'desc')->select();
            } else {
                $data['goodsAttr'] = [];
                $data['goodsSpec'] = [];
            }
            

            foreach($data['goodsAttr'] as &$item) {
                $item['attrContent'] = explode(",", $item['attrContent']);
                if ($item['attrType'] == 1) {
                    $item['value'] = '';
                }
                if ($item['attrType'] == 2) {
                    $item['value'] = $item['attrContent'][0];
                }
                if ($item['attrType'] == 3) {
                    $item['value'] = [];
                }
            }

            foreach($data['goodsSpec'] as &$item) {
                $item['specContent'] = explode(",", $item['specContent']);
                $item['value'] = [];
            }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}