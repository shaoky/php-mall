<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use think\facade\Log;
/**
 * @apiDefine adminSystemLogGroup admin-后台系统日志
 */

/**
 * @api {post} / 1. 后台登录日志
 * @apiName ad
 * @apiGroup adminSystemLogGroup
 * @apiSuccess {Number} goodsClassId 主键
 * @apiSuccess {Number} parentId 分类父Id
 * @apiSuccess {String} goodsClassName 广告标题
 * @apiSuccess {String} imageUrl 广告图片
 * @apiSuccess {Number} order 排序
 * @apiSuccess {Number} isOpen 是否开启：0关闭，1开启
 * @apiVersion 1.0.0
 */
class SystemLog extends Model {

    public function getAdminLoginList($params) {
        $where = [];
        if (!empty($params['begintime'])) {
            $where[] =  ['time', '>=', $params['begintime']];
        }
        if (!empty($params['endtime'])) {
            $where[] = ['time', '<=', $params['endtime']];
        }
        $where[] = ['action', '=', 'login'];
        try {
            $data['list'] = Db::name('admin_log')->where($where)->page($params['page'], $params['size'])->order('logid', 'desc')->select();
            $data['count'] = Db::name('admin_log')->where($where)->count();
            return $data;
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getAdminOperationList($params) {
        $where = [];
        if (!empty($params['begintime'])) {
            $where[] =  ['time', '>=', $params['begintime']];
        }
        if (!empty($params['endtime'])) {
            $where[] = ['time', '<=', $params['endtime']];
        }
        if (!empty($params['username'])) {
            $where[] = ['username', '=', $params['username']];
        }
        if (!empty($params['log_info'])) {
            $where[] = ['log_info', '=', $params['log_info']];
        }
        $where[] = ['action', '<>' , 'login'];
        try {
            $data['list'] = Db::name('admin_log')->where($where)->page($params['page'], $params['size'])->order('logid', 'desc')->select();
            $data['count'] = Db::name('admin_log')->where($where)->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}