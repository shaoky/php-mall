<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine adminAdGroup admin-广告模块
 */

/**
 * @api {post} / 1. 广告位置表
 * @apiName adPosition
 * @apiGroup adminAdGroup
 * @apiSuccess {Number} positionId 主键
 * @apiSuccess {String} title 广告位置标题
 * @apiSuccess {String} desc 广告位置描述
 * @apiSuccess {Number} width 广告位置宽度
 * @apiSuccess {Number} height 广告位置高度
 * @apiSuccess {String} mark 广告位置标识
 * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
 * @apiVersion 1.0.0
 */
class AdPosition extends Model {
    /**
     * @api {post} /admin/ad/position/add 1.1 广告位置新增
     * @apiName adPositionAdd
     * @apiGroup adminAdGroup
     * @apiParam {String} title 广告位置标题
     * @apiParam {String} [desc] 广告位置描述
     * @apiParam {Number} width 广告位置宽度
     * @apiParam {Number} height 广告位置高度
     * @apiParam {String} mark 广告位置标识
     * @apiParam {Number} isOpen 是否开启：0关闭，1开启
     * @apiVersion 1.0.0
     */
    public function add($params) {
        try {
            $data = $this->save($params);
            return '添加成功';
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/ad/position/list 1.2 广告位置列表
     * @apiName adPositionList
     * @apiGroup adminAdGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function list($params) {
        $data['count'] = $this->count();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = $data['count'];
        }
        $data['list'] = $this->page($params['page'], $params['size'])->select();
        $data['count'] = $this->count();
        return $data;
    }
    /**
     * @api {post} /admin/ad/position/update 1.3 广告位置更新
     * @apiName adPositionUpdate
     * @apiGroup adminAdGroup
     * @apiParam {Object} positionId 广告位置Id
     * @apiParam {Object} object 其他见新增接口
     * @apiVersion 1.0.0
     */
    public function updateAd($params) {
        try {
            $data = $this->where('positionId', $params['positionId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/ad/position/delete 1.4 广告位置删除
     * @apiName adPositionDelete
     * @apiGroup adminAdGroup
     * @apiParam {Number} positionId 位置Id
     * @apiVersion 1.0.0
     */
    public function deleteAd($params) {
        if (empty($params['positionId'])) {
            $this->error = '请选择要删除的广告位置';
            return false;
        }
        try {
            $data = $this->where('positionId', $params['positionId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '广告不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}