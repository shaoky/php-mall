<?php  
namespace app\admin\model;  
use app\admin\model\Common;
use think\Db;
use think\Model;
use think\Library;
use think\Request;
use app\admin\model\Log;
require __DIR__ . '/../Common/function.php';
/**
 * @apiDefine adminAdminGroup admin-管理员模块
 */
class Admin extends Common {
    // protected $pk = 'adminId';
    protected $request;
    public function __construct(Request $request)
    {
		$this->request = $request;
    }

    public function getAdmin($params) {
//        接口开始时间
        $nowTime = microtime(time());
        if (empty($params['loginName'])) {
            $this->error = '请输入用户名';
            return false;
        }
        if (empty($params['loginPwd'])) {
            $this->error = '请输入密码';
            return false;
        }
        $map['loginName'] = $params['loginName'];
        try {
            $result = db('admin')->where($map)->find();
            if (!$result) {
                $this->error = '账号不存在';
                return false;
            }
            if ($result['loginPwd'] !== user_md5($params['loginPwd'].$result['loginSecret'])) {
                $this->error = '密码错误';
                return false;
            }
            if ($result['status'] != 1) {
                $this->error = '该账号已经锁定了，请联系网站管理员';
                return;
            }
            $token = $this->request->token('__token__', 'jsjh_mall');
            Db::name('admin')
            ->where('adminId', $result['adminId'])
            ->update(['token' => $token]);
            //登录日志
            session('userid', $result['adminId']);
            session('username',$result['loginName']);
            session('token',$token);
//            session('roleid', $r['roleid']);

            $data = [
                'loginName' => $result['loginName'],
                'token' => $token,
                'level' => $result['level']
            ];
//            sleep(1);
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
        
        return $data;
    }
    /**
     * @api {post} /admin/user/admin/add 3. 新增管理员
     * @apiName addAdmin
     * @apiGroup adminAdminGroup
     * @apiParam {String} loginName 登录名称
     * @apiParam {String} loginPwd 密码,
     * @apiParam {String} realName 真实姓名
     * @apiParam {String} nickName 昵称
     * @apiParam {Number} status 1正常2离职3锁定
     * @apiParam {Number} grId 分组ID
     * @apiVersion 1.0.0
     */
    public function addAdmin($params) {
        if (empty($params['loginName'])) {
            $this->error = '请输入用户名';
            return false;
        }
        if (empty($params['loginPwd'])) {
            $this->error = '请输入密码';
            return false;
        }

        $adminInfo = db('admin')->where('loginName', $params['loginName'])->find();
        if ($adminInfo) {
            $this->error = '账号已存在';
            return false;
        }
        try {
            $loginSecret = rand(1000,9999);
            $form = [
                'loginName' => $params['loginName'],
                'loginPwd' => user_md5($params['loginPwd'].$loginSecret),
                'loginSecret' => $loginSecret,
                'realName' => $params['realName'],
                'nickName' => $params['nickName'],
                'grId' => $params['grId'],
                'status'=> 1,
            ];
            $data = db('admin')->insert($form);
            adminLog('后台注册','register',time());
        } catch (\Exception $e) {
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
        return '注册成功';
    }

    public function validationToken($token) {
        if (empty($token)) {
            $this->error = '请登录管理员账号';
        }
        try {
            $data = Db::name('admin')->where('token', $token)->find();
            if ($data['token'] == $token) {
                return $data;
            } else {
                $this->error = '登录超时，请重新登录';
            }
        } catch (\Exception $e) {
            echo 3;
            $this->error = $e->getMessage();
            return false;
        }
        
    }
    /**
     * @api {post} /admin/user/admin/list 1. 管理员列表
     * @apiName getAdminList
     * @apiGroup adminAdminGroup
     * @apiParam page 页码
     * @apiParam size 数量
     * @apiParam status 状态
     * @apiParam userName 查姓名，账号
     * @apiSuccess adminId ID
     * @apiSuccess loginName 登录名称
     * @apiSuccess realName 真实姓名
     * @apiSuccess nickName 昵称
     * @apiSuccess grId 分组ID
     * @apiSuccess status 1正常2离职3锁定
     * @apiVersion 1.0.0
     */
    public function getAdminList($params)
    {
        try {
            $where = [];
            if (!empty($params['userName'])) {
                $where[] = ['loginName|realName', 'like', '%'.$params['userName'].'%'];
            }
            if (!empty($params['status'])) {
                $where[] = ['status', '=', $params['status']];
            }
            $where[] = ['loginName', '<>', 'admin'];
            $result['list'] = Db::name('admin')->where($where)->field('adminId, grId, loginName, nickName, realName, status')->page($params['page'], $params['size'])->select();
            foreach($result['list'] as $key => $item) {
                $result['list'][$key]['selectedRole'] = $this->getGroupRecursion($item['grId']);
            }
            $result['count'] = Db::name('admin')->where($where)->count();
            return $result;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getGroupRecursion ($id, $list = []) {
        if ($id) {
            $group = Db::name('group')->where('grId', $id)->find();
            $list[] = $group['grId'];
            if ($group['grPid'] != 0) {
                return $this->getGroupRecursion($group['grPid'], $list);
            }
        }
        return array_reverse($list);
    }
        /**
         * @api {post} /admin/user/admin/update 2. 修改管理员信息
         * @apiName updateAdmin
         * @apiGroup adminAdminGroup
         * @apiParam {String} loginName 登录名称
         * @apiParam {String} realName 真实姓名
         * @apiParam {String} nickName 昵称
         * @apiParam {Number} status 1正常2离职3锁定
         * @apiParam {Number} grId 分组id
         * @apiParam {Number} adminId id
         * @apiVersion 1.0.0
         */
        public function updateAdmin($params)
        {

            try {
                $id = $params['adminId'];
                unset($params['adminId']);
                $res = Db::name('admin')->data($params)->where('adminId',$id)->update();
                if ($res > 0) {
                    return '操作成功';
                }else{
                    return '操作失败';
                }
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }
        /**
         * @api {post} /admin/user/admin/delete 4. 删除管理员信息
         * @apiName deleteAdmin
         * @apiGroup adminAdminGroup
         * @apiParam {Number} adminId id
         * @apiVersion 1.0.0
         */
        public function deleteAdmin($params)
        {

            try {
                $res = Db::name('admin')->where($params)->delete();
                if ($res > 0) {
                    return '操作成功';
                }else{
                    return '操作失败';
                }
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        /**
         * @api {post} /admin/user/admin/info 5. 获取管理员信息
         * @apiName adminInfo
         * @apiGroup adminAdminGroup
         * @apiParam {Number} adminId id
         * @apiVersion 1.0.0
         */
        public function adminInfo()
        {
            $adminInfo = $this->getAdminInfo();
            try {
                // $data['info'] = Db::name('admin')->where('adminId', $adminInfo['adminId'])->find();
                return $adminInfo;
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        /**
         * @api {post} /admin/user/admin/password 5. 修改管理员密码
         * @apiName passwordAdmin
         * @apiGroup adminAdminGroup
         * @apiParam {Number} adminId id
         * @apiVersion 1.0.0
         */
        public function passwordAdmin($params)
        {
            $adminInfo = $this->getAdminInfo();
            if (empty($params['loginPwd'])) {
                $this->error = '请输入旧密码';
                return;
            }
            if (empty($params['newLoginPwd'])) {
                $this->error = '请输入新密码';
                return;
            }

            Db::startTrans();
            try {
                $result = Db::name('admin')->where('adminId', $adminInfo['adminId'])->find();
                if ($result['loginPwd'] !== user_md5($params['loginPwd'].$result['loginSecret'])) {
                    $this->error = '密码错误';
                    return false;
                }
                $loginSecret = rand(1000,9999);
                $loginPwd = user_md5($params['newLoginPwd'].$loginSecret);
                $data = Db::name('admin')->where('adminId', $adminInfo['adminId'])->update(['loginPwd' => $loginPwd, 'loginSecret' => $loginSecret]);
                Db::commit();
                if ($data == 1) {
                    return '修改成功';
                } else {
                    $this->error = '修改失败';
                    return;
                }
                
            } catch (\Exception $e) {
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }

        /**
         * @api {post} /admin/user/admin/passwordReset 6. 重置员工密码
         * @apiName passwordReset
         * @apiGroup adminAdminGroup
         * @apiParam {Number} adminId id
         * @apiVersion 1.0.0
         */
        public function passwordReset($params)
        {
            Db::startTrans();
            try {
                $loginSecret = rand(1000,9999);
                $loginPwd = user_md5('123456'.$loginSecret);
                $data = Db::name('admin')->where('adminId', $params['adminId'])->update(['loginPwd' => $loginPwd, 'loginSecret' => $loginSecret]);
                Db::commit();
                if ($data == 1) {
                    return '重置成功';
                } else {
                    $this->error = '重置失败';
                    return;
                }
                
            } catch (\Exception $e) {
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }

}