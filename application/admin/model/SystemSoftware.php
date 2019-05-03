<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
class SystemSoftware extends Model {

    public function add($params) {
        try {
            $params['createTime'] = time();
            $data = Db::name('software')->insert($params);
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
        if (isset($params['isOpen'])) {
            $where['isOpen'] = $params['isOpen'];
        } 
        // if (isset($params['positionId'])) {
        //     $where['a.positionId'] = $params['positionId'];
        // }
        $data['list'] = Db::name('software')->where($where)->select();
        $data['count'] = Db::name('software')->where($where)->count();
        // foreach($data['list'] as $key=>$item) {
        //     $data['list'][$key]['children'] = $this->where('parentId', $item['goodsClassId'])->select();
        // }
        return $data;
    }

    public function updateSoftware($params) {
        if (empty($params['softwareId'])) {
            $this->error = '请选择要更新的软件';
            return false;
        }
        try {
            $data = Db::name('software')->where('softwareId', $params['softwareId'])->update($params);
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

    public function deleteSoftware($params) {
        if (empty($params['storeId'])) {
            $this->error = '请选择要删除的店铺';
            return false;
        }
        try {
            $data = Db::name('software')->where('storeId', $params['storeId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                $this->error = '该店铺不存在';
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