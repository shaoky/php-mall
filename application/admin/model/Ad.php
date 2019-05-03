<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;

/**
 * @apiDefine adminAdGroup admin-广告模块
 */

/**
 * @api {post} / 2. 广告表
 * @apiName ad
 * @apiGroup adminAdGroup
 * @apiSuccess {Number} adId 主键
 * @apiSuccess {String} title 广告标题
 * @apiSuccess {String} imageUrl 广告图片
 * @apiSuccess {Number} type 广告类型：1产品，2网页，3内页
 * @apiSuccess {String} operation 广告操作
 * @apiSuccess {Number} order 排序
 * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
 * @apiVersion 1.0.0
 */
class Ad extends Common {
    // protected $autoWriteTimestamp = true;
    // protected $createTime = 'createTime';

    public function addAd($params) {
        try {
            $data = $this->save($params);
            return '添加成功';
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getAdList($params) {
        if (empty($params['title'])) {
            $params['title'] = '';
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        $where = [];
        if (isset($params['isOpen'])) {
            $where['a.isOpen'] = $params['isOpen'];
        }
        if (isset($params['positionId'])) {
            $where['a.positionId'] = $params['positionId'];
        }
        $data['list'] = $this->alias('a')
            ->join('adPosition b', 'a.positionId = b.positionId')
            ->field('a.*, b.title as positionTitle')
            ->whereLike('a.title', '%'.$params['title'].'%')
            ->where($where)
            ->order('sort desc')
            ->page($params['page'], $params['size'])
            ->select();
        // halt($data['list']);
        foreach($data['list'] as $item) {
            $item['isOpen'] = getStatusName('isOpen', $item['isOpen']);
        }
        $data['count'] = $this->whereLike('a.title', '%'.$params['title'].'%')->where($where)->alias('a')->join('adPosition b', 'a.positionId = b.positionId')->field('a.*, b.title as positionTitle')->count();
        return $data;
    }

    public function updateAd($params) {
        try {
            $data = $this->where('adId', $params['adId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                // $this->error = '更新失败';
                return '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteAd($params) {
        if (empty($params['adId'])) {
            $this->error = '请选择要删除的广告';
            return false;
        }
        try {
            $data = $this->where('adId', $params['adId'])->delete();
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

    public function getAdInfo($params) {
        if (empty($params['adId'])) {
            $this->error = '广告id不存在';
            return false;
        }
        try {
            $data['info'] = $this->where('adId', $params['adId'])->find();
            // echo $data['info'];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}