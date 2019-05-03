<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/24
 * Time: 11:19
 */

namespace app\admin\model;


use think\Db;
use think\Model;
//use think\Request;
use think\facade\Request;
use app\admin\model\Common;


/**
 * @apiDefine adminAuthGroup admin-权限模块
 */
class Auth extends Common
{
    /**
     * @api {post} /admin/auth/column/index 1.1 栏目列表
     * @apiName getList
     * @apiGroup adminAuthGroup
     * @apiSuccess columnId ID
     * @apiSuccess columnName 栏目名称
     * @apiSuccess columnURL 栏目URL,
     * @apiSuccess columnPid 栏目父id,
     * @apiSuccess sort 排序
     * @apiVersion 1.0.0
     */
    public function getList(){
        $filed = "columnId,columnName,columnURL,columnPid,sort";
        $result = Db::name('column')->field($filed)->order('sort', 'desc')->select();
        $column['list'] = $this->getNext($result);
        // for ($i=0;$i<count($column['list']);$i++){
        //     $column['list'][$i]['children'] = $this->getNext($column['list'][$i]['columnId']);

        // }
        return $column;
    }

    public function getNext ($array, $pid = 0, $level = 0) {
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        $list = [];
        // $columnList = Db::name('column')->where('columnPid', $pid)->select();
        foreach ($array as $key => $value){
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['columnPid'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $value['children'] = $this->getNext($array, $value['columnId'], $level+1);
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            }
        }
        return $list;
    }
//获取二级及以上栏目 最多三级
//     public function getNext($pid){
//         $filed = "columnId,columnName,columnURL,columnPid";
//         $res = Db::name('column')->field($filed)->where('columnPid',$pid)->select();
//         if (!empty($res)){
// //            三级
//             for($a=0;$a<count($res);$a++){
//                   $three  = Db::name('column')->field($filed)->where('columnPid',$res[$a]['columnId'])->select();
//                 if (count($three) > 0){
//                     for($c=0;$c<count($three);$c++){
// //                        var_dump(count($three));
//                         if(!empty($three[$c])){
//                             $three[$c]['authlist'] = $this->getAuth($three[$c]['columnId']);
//                         }
//                     }
//                 }
//                 $res[$a]['children'] = $three;

//             }
//         }
//         return $res;
//     }
    // 获取权限列表
    public function getAuth($columnId){
        $where['columnId'] = $columnId;
        $where['auPid'] = 0;
        $field = "auId,auName,auTitle as label,auStatus,auRemarks,auSort,auPid,auLevel,auType,grId,columnId";
        $res = Db::name('auth')->field($field)->where($where)->select();
        for($i=0;$i<count($res);$i++){
            $res[$i]['authlist'] = $this->getNextAuth($res[$i]['auId']);
        }
        return $res;
    }
    // 获取二级和三级权限列表
    public function getNextAuth($auPid){
        $field = "auId,auName,auTitle as label,auStatus,auRemarks,auSort,auPid,auLevel,auType,grId,columnId";
        $res = Db::name('auth')->field($field)->where('auPid',$auPid)->select();
        if (!empty($res)){
            for($a=0;$a<count($res);$a++){
                $res[$a]['authlist'] = Db::name('auth')->field($field)->where('auPid',$res[$a]['auId'])->select();
            }
        }
        return $res;
    }
    /**
     *  @api {post} /admin/auth/column/add 1.2 新增栏目
     * @apiName addColumn
     * @apiGroup adminAuthGroup
     * @apiParam {String} columnName 栏目名称
     * @apiParam {String} columnURL 栏目URL 栏目为一级是为空
     * @apiParam {Number} columnPid 栏目父ID栏目为一级是为0
     */
    public function addColumn($request){
        if(!$request->has('columnName')){
            $this->error = '栏目名称不能为空';
            return false;
        }
        // if(!$request->has('columnURL')){
        //     $this->error = '网页url名称不能为空';
        //     return false;
        // }
        $arr['columnName'] = $request->post('columnName');
        if (!empty($request->post('columnURL'))) {
            $arr['columnURL'] = $request->post('columnURL');
        }
        $arr['columnPid'] = $request->post('columnPid');
        $arr['sort'] = $request->post('sort');
        // $arr['columnLevel'] = $request->post('columnLevel');
        try{
            $data = Db::name('column')->data($arr)->insert();
            if ($data > 0 ){
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
     *  @api {post} /admin/auth/column/delete 1.3 删除栏目
     * @apiName deleteColumn
     * @apiGroup adminAuthGroup
     * @apiParam {Number} columnId 栏目Id
     */
    public function deleteColumn($request){
        if(!$request->has('columnId')){
            $this->error = '请传columnId';
            return false;
        }
        Db::startTrans();
        try{
            $columnList = Db::name('column')->where('columnPid', $request->post('columnId'))->select();
            if (count($columnList) > 0) {
                $this->error = '请先删除下级的栏目';
                return;
            }
            $data = Db::name('column')->where('columnId', $request->post('columnId'))->delete();
            $adminColumn = Db::name('admin_column')->select();
            foreach($adminColumn as $key => $item) {
                $columnIds = json_decode($item['columnId']);
                foreach($columnIds as $key1 => $item1) {
                    if ($request->post('columnId') == $item1) {
                        unset($columnIds[$key1]);
                        Db::name('admin_column')->where('acId', $item['acId'])->update([
                            'columnId' => json_encode($columnIds)
                        ]);
                    }
                }
            }
            Db::commit();
            if ($data > 0 ){
                return '删除成功';
            }else{
                return '删除失败';
            }
        }catch (\Exception $e){
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     *  @api {post} /admin/auth/column/update 1.4 更新栏目
     * @apiName updateColumn
     * @apiGroup adminAuthGroup
     * @apiParam {String} columnName 栏目名称
     * @apiParam {String} columnURL 栏目URL 栏目为一级是为空
     * @apiParam {Number} columnPid 栏目父ID栏目为一级是为0
     */
    public function updateColumn($request){
        if(!$request->has('columnId')){
            $this->error = '请传columnId';
            return false;
        }
        try{
            $arr['columnName'] = $request->post('columnName');
            $arr['columnURL'] = $request->post('columnURL');
            $arr['columnPid'] = $request->post('columnPid');
            $arr['sort'] = $request->post('sort');
            $data = Db::name('column')->where('columnId', $request->post('columnId'))->update($arr);
            if ($data > 0 ){
                return '更新成功';
            }else{
                return '更新失败';
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     *  @api {post} /admin/auth/add 3. 暂时废弃-新增权限
     * @apiName addAuth
     * @apiGroup adminAuthGroup
     * @apiParam {String} auName 名称
     * @apiParam {String} auTitle 标题
     * @apiParam {Number} auStatus 状态1启用2不启用
     * @apiParam {String} auRemarks 备注
     * @apiParam {Number} auSort 排序
     * @apiParam {Number} auPid 父ID 默认为0
     * @apiParam {Number} auType 类型默认为1
     * @apiParam {Number} grId 分组ID 类型默认为0
     * @apiParam {Number} columnId 栏目ID类型默认为0
     */
    public function addAuth($resquest){
        if (!$resquest->has('auName')){
            $this->error = '名称不能为空';
            return false;
        }
        if (!$resquest->has('auTitle')){
            $this->error = '标题不能为空';
            return false;
        }
        if (!$resquest->has('columnId') && $resquest->post('columnId') == 0){
            $this->error = '栏目不能为空';
            return false;
        }
        $data['auName'] = $resquest->post('auName');
        $data['auTitle'] = $resquest->post('auTitle');
        $data['auStatus'] = $resquest->post('auStatus');
        $data['auRemarks'] = $resquest->post('auRemarks');
        $data['auSort'] = $resquest->post('auSort');
        $data['auPid'] = $resquest->post('auPid');
        $data['auType'] = $resquest->post('auType',1);
        $data['grId'] = $resquest->post('grId',0);
        $data['columnId'] = $resquest->post('columnId',0);
        try{
            $res = Db::name('auth')->data($data)->insert();
            if ($res > 0) {
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
     *  @api {post} /admin/auth/column/getuser 2.1 角色栏目权限列表
     * @apiName getUserColumnList
     * @apiGroup adminAuthGroup
     * @apiParam {Number} grId 分组ID
     */
    public function getUserColumnList($params){
        try {
            $columnList['info'] = Db::name('admin_column')->where('grId', $params['grId'])->find();
            return $columnList;
        } catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     *  @api {post} /admin/auth/column/adduser 2.2 新增角色栏目权限
     * @apiName addColumnUserAuth
     * @apiGroup adminAuthGroup
     * @apiParam {Number} columnId 栏目ID 多个栏目逗号拼接
     * @apiParam {Number} grId 分组ID
     */
    public function addColumnUserAuth($params){
        if (empty($params['columnId'])) {
            $this->error = '请传columnId';
            return;
        }
        if (empty($params['grId'])) {
            $this->error = '请传grId';
            return;
        }
        try {
            $data = Db::name('admin_column')->where('grId', $params['grId'])->find();
            if ($data) {
                $result = Db::name('admin_column')->where('grId', $params['grId'])->update([
                    'columnId' => $params['columnId'],
                    'grId' => $params['grId']
                ]);
            } else {
                $result = Db::name('admin_column')->insert($params);
            }
            if ($result) {
                return '操作成功';
            } else {
                $this->error ='操作失败';
            }
        } catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     *  @api {post} /admin/auth/adduser 5. 暂时废弃-新增用户权限
     * @apiName addUserAuth
     * @apiGroup adminAuthGroup
     * @apiParam {Number} auId 权限ID 多个栏目逗号拼接
     * @apiParam {Number} grId 分组ID
     */
    public function addUserAuth($request){
        $columnIds = $request['auId'];
        $columnId = explode(",",$columnIds);
        try{
            for($i=0;$i<count($columnId);$i++){
                $arr['auId'] = $columnId[$i];
                $arr['grId'] = $request['grId'];
                Db::name('admin_auth')->data($arr)->insert();
            }
        }catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
    }


    /**
     *  @api {post} /admin/auth/column/user 2.3 获取当前登录用户的栏目列表
     * @apiName getUserColumn
     * @apiGroup adminAuthGroup
     * @apiParam {String} token token
     */
    public function getUserColumn(){
        $adminInfo = $this->getAdminInfo();
        try {
            if ($adminInfo['level'] == 2) {
                $data = $this->getList();
                return $data['list'];
            }
            $column = Db::name('admin_column')->where('grId', $adminInfo['grId'])->find();
            $columnIds = json_decode($column['columnId']);
            if (!$column['columnId'] || count($columnIds) == 0) {
                $group = Db::name('group')->where('grId', $adminInfo['grId'])->find();
                $column = Db::name('admin_column')->where('grId', $group['grPid'])->find();
                if (!$column['columnId']) {
                    $this->error = '当前角色请设置页面权限';
                    return;
                }
                $columnIds = json_decode($column['columnId']);
            }
            $columnIdArr = implode(",", $columnIds);
            // dump($columnIdArr);
            $columnList = Db::name('column')->where('columnId', 'in', $columnIdArr)->order('sort', 'desc')->select();
            // dump($columnList);
            foreach ($columnList as $key => $item) {
                // 1. 拿到列表  2. 根据pid查他的上级，如果上级是0不查 3. 查到之后，加到这个列表里，如果有了，不加，4. 递归查
                $list = $this->getUserNext($item['columnPid']);
                foreach($list as $key1 => $item1) {
                    if (!in_array($item1, $columnList)) {
                        $columnList[] = $item1;
                    }
                }
            }
            return $this->getNext($columnList);
        } catch (\Exception $e){
            $this->error = $e->getMessage();
            return false;
        }
//         $header = Request::instance()->header();
//         $authorization = $header['authorization'];
//         $admin = db('user')->where('token', $authorization)->find();
//         $grId = Db::name('admin_group')->where('adminId',$admin['adminId'])->find();
//         $where['b.grId'] = $grId['grId'];
//         $where['a.auPid'] = 0;
// //        一级的
//         $res['list'] = Db::name('column')
//             ->alias('a')
//             ->join(['tp_admin_column'=> 'b'],'a.columnId=b.columnId')
//             ->where($where)
//             ->select();
//         for ($i=0;$i<count($res['list']);$i++){
//             $column['list'][$i]['children'] = $this->getUserNext($res['list'][$i]['columnId']);

//         }
//         return $res;
        /*if (!empty($res)){
            return true;
        }else{
            return false;
        }*/
    }
    public function getUserNext ($pid, $list = []) {
        $column = Db::name('column')->where('columnId', $pid)->find();
        if ($pid != 0) {
            $list[] = $column;
            return $this->getUserNext($column['columnPid'], $list);
        }
        return $list;
    }
    //获取二级及以上栏目 最多三级
//     public function getUserNext($pid){
//         $filed = "columnId,columnName as label,columnURL,columnPid";
//         $res = Db::name('column')->field($filed)->where('columnPid',$pid)->select();
//         if (!empty($res)){
// //            三级
//             for($a=0;$a<count($res);$a++){
//                 $three  = Db::name('column')->field($filed)->where('columnPid',$res[$a]['columnId'])->select();
//                 $res[$a]['children'] = $three;
//             }
//         }
//         return $res;
//     }

    // 判断当前用户是否有这个权限
    public function getUserAuth($Url){
        $header = Request::instance()->header();
        $authorization = $header['authorization'];
        $admin = db('user')->where('token', $authorization)->find();
        if ($admin['loginName'] == 'admin'){
            return true;
        }
        $grId = Db::name('admin_group')->where('adminId',$admin['adminId'])->find();
        $where['b.grId'] = $grId['grId'];
        $where['a.auName'] = $Url;
        // 一级的
        $res = Db::name('auth')
            ->alias('a')
            ->join(['tp_admin_auth'=> 'b'],'a.auId=b.auId')
            ->where($where)
            ->select();

        if (!empty($res)){
            return true;
        }else{
            return false;
        }
    }

}