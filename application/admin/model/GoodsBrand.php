<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */


class GoodsBrand extends Model {
    /**
     * @api {post} /admin/goods/brand/add 3.1 品牌新增
     * @apiName addBrandData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {String} brandName 品牌名称
     * @apiSuccess {String} brandLetter 品牌首字母
     * @apiSuccess {String} brandImage 品牌图片
     * @apiSuccess {String} brandDesc 品牌介绍
     * @apiSuccess {Array} list 分类id列表 [1,2,3]
     * @apiSuccess {Number} brandSort 品牌排序
     * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
     * @apiVersion 1.0.0
     */
    public function addData($params) {
        try {
            $params['createTime'] = time();
            $data = $this->save($params);
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /admin/goods/brand/list 3.2 品牌列表
     * @apiName getBrandList
     * @apiGroup adminGoodsGroup
     * @apiVersion 1.0.0
     */
    public function getList($params) {
        $data['list'] = $this->order('brandSort', 'desc')->select();
        foreach($data['list'] as &$item) {
            $item['isOpen'] = getStatusName('isOpen', $item['isOpen']);
        }
        return $data;
    }

    /**
     * @api {post} /admin/goods/brand/update 3.3 品牌更新
     * @apiName updateBrandData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} brandId 品牌Id
     * @apiSuccess {String} brandName 品牌名称
     * @apiSuccess {String} brandLetter 品牌首字母
     * @apiSuccess {String} brandImage 品牌图片
     * @apiSuccess {String} brandDesc 品牌介绍
     * @apiSuccess {Array} list 分类id列表 [1,2,3]
     * @apiSuccess {Number} brandSort 品牌排序
     * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
     * @apiVersion 1.0.0
     */
    public function updateData($params) {
        if (empty($params['brandId'])) {
            $this->error = '商品分类id出错了';
            return false;
        }
        try {
            $data = $this->where('brandId', $params['brandId'])->update($params);
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
     * @api {post} /admin/goods/brand/delete 3.4 品牌删除
     * @apiName deleteBrandData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} brandId 主键
     * @apiVersion 1.0.0
     */
    public function deleteData($params) {
        if (empty($params['brandId'])) {
            $this->error = '请选择要删除的品牌';
            return false;
        }

        Db::startTrans();
        try {
            $data = Db::name('goods_brand')->where('brandId', $params['brandId'])->delete();
            Db::commit();
            if ($data == 1) {
                return '删除成功';
            } else {
                $this->error = '品牌不存在';
            }
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /admin/goods/brand/info 3.5 品牌详情
     * @apiName getBrandData
     * @apiGroup adminGoodsGroup
     * @apiSuccess {Number} brandId 主键
     * @apiVersion 1.0.0
     */
    // public function getData($params) {
    //     if (empty($params['goodsClassId'])) {
    //         $this->error = '商品分类id出错了';
    //         return false;
    //     }
    //     try {
    //         $data['info'] = $this->where('goodsClassId', $params['goodsClassId'])->find();
    //         return $data;
    //     } catch (\Exception $e) {
    //         $this->error = $e->getMessage();
    //         return false;
    //     }
    // }
}