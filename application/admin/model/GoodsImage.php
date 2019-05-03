<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminCartGroup admin-商品模块
 */

/**
 * {post} / 0. 商品表
 * @apiName goods
 * @apiGroup adminGoodsGroup
 * @apiSuccess {Number} goodsId 商品id
 * @apiSuccess {String} goodsName 商品标题
 * @apiSuccess {String} goodsSubtitle 副标题
 * @apiSuccess {String} goodsImage 商品主图
 * @apiSuccess {String} goodsThums 商品缩略图
 * @apiSuccess {Number} marketPrice 商品市场价
 * @apiSuccess {Number} shopPrice 商品价格
 * @apiSuccess {Number} goodsStock 商品库存
 * @apiSuccess {Number} saleCount 商品销量
 * @apiSuccess {Number} createTime 创建时间
 * @apiSuccess {Number} order 排序
 * @apiSuccess {Number} isOpen 是否开启
 * @apiVersion 1.0.0
 */
class GoodsImage extends Model {
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createTime';

    public function addImage($params) {
        if (empty($params['goodsId'])) {
            $this->error = '商品id出错了';
            return false;
        }
        $params['createTime'] = time();
        $params['order'] = 100;
        // dump($params);
        try {
            $data = $this->save($params);
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteImage($params) {
        if (empty($params['id'])) {
            $this->error = '商品id出错了';
            return false;
        }
        try {
            $data = $this->where('id', $params['id'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '图片不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}