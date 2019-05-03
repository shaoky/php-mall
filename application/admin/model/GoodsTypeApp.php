<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */


class GoodsTypeApp extends Model {
    /**
     * @api {post} /admin/goods/typeApp/add 2.1 前台类目新增
     * @apiName addData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {String} typeName 分类名称
     * @apiSuccess {Number} parentId 分类父Id
     * @apiSuccess {Number} typeSort 分类排序
     * @apiSuccess {Array} list 关联基础类目, [1,2,3,4]
     * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
     * @apiVersion 1.0.0
     */
    public function addData($params) {
        try {
//            $data = $this->save($params);
            $array = $params['list'];
            unset($params['list']);
            $data = $this->insertGetId($params);
            foreach ($array as $key=>$v){
                $insert['typeId'] = $data;
                $insert['goodsClassId'] = $array[$key];
                Db::name('goods_type_app_relation')->insert($insert);
            }
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /admin/goods/typeApp/list 2.2 前台类目列表
     * @apiName getList
     * @apiGroup adminGoodsGroup
     * @apiParam {Int} page 页码
     * @apiParam {Int} size 数量
     * @apiVersion 1.0.0
     */
    public function getList($params) {
        $where = [];
        
//        if (isset($params['goodsClassId'])) {
//            $where['parentId'] = $params['goodsClassId'];
//        } else {
//            $where['parentId'] = 0;
//        }
//
//        if (isset($params['isOpen'])) {
//            $where['isOpen'] = $params['isOpen'];
//        }
        $data['list'] = $this->where($where)->order('sort', 'desc')->limit($params['page'],$params['size'])->select();
        /*foreach($data['list'] as $key=>$item) {
            $data['list'][$key]['isOpen'] = getStatusName('isOpen', $item['isOpen']);
            $where1['parentId'] = $item['goodsClassId'];
            if (isset($params['isOpen'])) {
                $where1['isOpen'] = $params['isOpen'];
            }
            $data['list'][$key]['children'] = $this->where($where1)->select();
            foreach($data['list'][$key]['children'] as $key1 => $item1) {
                $data['list'][$key]['children'][$key1]['isOpen'] = getStatusName('isOpen', $item1['isOpen']);
            }
        }*/
        return $data;
    }

    /**
     * @api {post} /admin/goods/typeApp/update 2.3 前台类目更新
     * @apiName updateData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} typeId 分类Id
     * @apiSuccess {String} typeName 分类名称
     * @apiSuccess {Number} parentId 分类父Id
     * @apiSuccess {Number} typeSort 分类排序
     * @apiSuccess {Array} list 关联基础类目, [1,2,3,4]
     * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
     * @apiVersion 1.0.0
     */
    public function updateData($params) {
//        if (empty($params['goodsClassId'])) {
//            $this->error = '商品分类id出错了';
//            return false;
//        }
        try {
            $array = $params['list'];
            unset($params['list']);
            $data = $this->where('typeId', $params['typeId'])->update($params);
            Db::name('goods_type_app_relation')->where("typeId",$params['typeId'])->delete();
            foreach ($array as $key=>$v){
                $insert['typeId'] = $params['typeId'];
                $insert['goodsClassId'] = $array[$key];
                Db::name('goods_type_app_relation')->insert($insert);
            }

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

    /**
     * @api {post} /admin/goods/typeApp/delete 2.4 前台类目删除
     * @apiName deleteData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} typeId 主键
     * @apiVersion 1.0.0
     */
    public function deleteData($params) {
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

    /**
     * @api {post} /admin/goods/typeApp/info 2.5 前台类目详情
     * @apiName getData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} typeId 主键
     * @apiVersion 1.0.0
     */
    public function getData($params) {
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