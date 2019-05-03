<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25
 * Time: 14:49
 */

namespace app\admin\model;


use think\Db;
use think\Model;
/**
 * @apiDefine adminGroupsGroup admin-管理员分组模块
 */
class Group extends Model
{
    /**
     *  {post} /admin/group/list 7. 角色分组列表
     * @apiName getGroupList
     * @apiGroup adminGroupsGroup
     */
    public function getGroupList($params){
        try{
            $result = Db::name('group')->field('grId, grName, grPid, grRemarks')->select();
            $data['list'] = $this->getTree($result);
            return $data;
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    public function getTree($array, $pid = 0, $level = 0){
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        $list = [];
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['grPid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $value['children'] = $this->getTree($array, $value['grId'], $level+1);
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                // unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
                

            }
        }
        return $list;
    }

    /**
     *  {post} /admin/group/add 8. 角色分组新增
     * @apiName addGroup
     * @apiGroup adminGroupsGroup
     * @apiParam {String} grName 分组名称
     * @apiParam {Number} grPid 分组父ID
     * @apiParam {Number} grStatus 1启用2不启用 可忽略
     * @apiParam {String} grRemarks 备注
     */
    public function addGroup($params){
        if ($params['grName'] == ""){
            $this->error = '分组名称不能为空';
            return false;
        }
        try{
            $params['grCreateTime'] = time();
            $res = Db::name('group')->data($params)->insert();
            if ($res > 0){
                return '新增成功';
            }else{
                return '新增失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     *  {post} /admin/group/update 8. 角色分组编辑
     * @apiName updateGroup
     * @apiGroup adminGroupsGroup
     * @apiParam {String} grName 分组名称
     * @apiParam {Number} grPid 分组父ID
     * @apiParam {Number} grId 分组ID
     * @apiParam {Number} grStatus 1启用2不启用 可忽略
     * @apiParam {String} grRemarks 备注
     */
    public function updateGroup($params){
        try{
            $params['grModified'] = time();
            $id = $params['grId'];
            unset($params['grId']);
            $res = Db::name('group')->data($params)->where("grId",$id)->update();
            if ($res > 0){
                return '操作成功';
            }else{
                return '操作失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     *  {post} /admin/group/delete 9. 角色分组删除
     * @apiName deleteGroup
     * @apiGroup adminGroupsGroup
     * @apiParam {Number} grId 分组ID
     */
    public function deleteGroup($params){
        try{
            $res = Db::name('group')->where($params)->delete();
            if ($res > 0){
                return '操作成功';
            }else{
                return '操作失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
}