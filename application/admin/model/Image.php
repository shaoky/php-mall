<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;

/**
 * @apiDefine adminImageGroup admin-图片
 */

/**
 * @api {post} / 1. 图片表
 * @apiName ad
 * @apiGroup adminAdGroup
 * @apiSuccess {Number} imageId 主键
 * @apiSuccess {Number} fromUserType 来自哪，1：平台管理员，2：商家/用户
 * @apiSuccess {Number} dataId 该图所在表的id
 * @apiSuccess {String} imageUrl 图片路径
 * @apiSuccess {String} imageName 图片名称
 * @apiSuccess {String} fromTable 来自哪张表
 * @apiSuccess {Number} userId 用户id
 * @apiSuccess {Number} fieldType 类型，表中有多个图片字段用123....区分
 * @apiSuccess {Number} createTime 创建时间
 * 
 * @apiVersion 1.0.0
 */
class Image extends Common {

    public function addImage($params) {
        $userInfo = $this->getAdminInfo();
        $params['createTime'] = time();
        $params['sort'] = 100;
        $params['userId'] = $userInfo['adminId'];
        try {
            $data = Db::name('image')->insert($params);
            return '添加成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getImageList($params) {
        try {
            $where = [
                'fromTable' => $params['fromTable'],
                'dataId' => $params['dataId'],
                'fieldType' => $params['fieldType'],
                'isDelete' => 0
            ];
            $data['list'] = Db::name('image')->where($where)->select();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteImage($params) {
        try {
            $where = [
                'imageId' => $params['imageId'],
                'fromTable' => $params['fromTable'],
                'dataId' => $params['dataId']
            ];
            $data = Db::name('image')->where($where)->update(['isDelete' => 1]);
            if ($data) {
                return '删除成功';
            } else {
                return '删除失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

}