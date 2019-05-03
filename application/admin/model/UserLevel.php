<?php  
namespace app\admin\model;  
use think\Db;
use app\admin\model\Common;
/**
 * @apiDefine adminUserGroup admin-用户
 */

class UserLevel extends Common {
    /**
     * @api {post} /admin/user/level/list 1.1 会员等级列表
     * @apiName userLevel
     * @apiGroup adminUserGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function list($params) {
        try {
            $data['list'] = db('userLevel')->field('levelId,levelName,needMoney,userType,needPlatinumPeopel,needGoldPeople,needGoldTeamPeople')->select();
            $data['count'] = db('userLevel')->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/user/level/add 1.2 会员等级新增/更新
     * @apiName userLevel
     * @apiGroup adminUserGroup
     * @apiParam {String} levelId 会员等级id
     * @apiParam {String} levelName 会员等级名称
     * @apiParam {String} needMoney 累积升级金额
     * @apiParam {Number} level 等级Level
     * @apiVersion 1.0.0
     */
    public function add($params) {
        if (empty($params['levelId'])) {
            try {
                $data = db('userLevel')->insert($params);
                return '添加成功';
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        try {
            $data = db('userLevel')->update($params);
            return '更新成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/user/level/delete 1.3 会员等级详情
     * @apiName getInfo
     * @apiGroup adminUserGroup
     * @apiParam {Number} levelId 会员等级id
     * @apiVersion 1.0.0
     */
    public function getInfo($params) {
        try {
            $data['info'] = db('userLevel')->where('levelId', $params['levelId'])->find();
            if ($data) {
                return $data;
            } else {
                return '会员等级不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /admin/user/level/delete 1.4 会员等级删除
     * @apiName userLevel
     * @apiGroup adminUserGroup
     * @apiParam {Number} levelId 会员等级id
     * @apiVersion 1.0.0
     */
    public function deleteLevel ($params) {
        if (empty($params['levelId'])) {
            $this->error = '会员等级id不存在';
        }

        try {
            $data = db('userLevel')->where('levelId', $params['levelId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '会员等级不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}