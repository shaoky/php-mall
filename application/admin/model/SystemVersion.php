<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
class SystemVersion extends Model {

    public function add($params) {
        try {
            $params['createTime'] = time();
            $data = Db::name('version')->insert($params);
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
        $data['list'] = Db::name('version')
            ->where($where)
            ->page($params['page'], $params['size'])
            ->order('versionId', 'desc')
            ->select();
        $data['count'] = Db::name('version')->where($where)->count();
        // foreach($data['list'] as $key=>$item) {
        //     $data['list'][$key]['children'] = $this->where('parentId', $item['goodsClassId'])->select();
        // }
        return $data;
    }

    public function updateVersion($params) {
        if (empty($params['versionId'])) {
            $this->error = '请选择要更新的软件';
            return false;
        }
        try {
            $params['updateTime'] = time();
            $data = Db::name('version')->where('versionId', $params['versionId'])->update($params);
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

    public function deleteVersion($params) {
        if (empty($params['versionId'])) {
            $this->error = '请选择要删除的店铺';
            return false;
        }
        try {
            $data = Db::name('version')->where('versionId', $params['versionId'])->delete();
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