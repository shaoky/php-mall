<?php 
// 用户
namespace app\admin\controller;
use think\facade\Request;
use think\Controller;
use think\Db;

class ApiCommon extends Controller {
    public function initialize() {
        $this->nowTime = microtime(time());
        $request = Request::instance();
        $header = $request->header();
        $controller = $request->controller();
        header("Access-Control-Allow-Origin: *");

        $list = ['Base'];
        if (in_array($controller, $list)) {
            return;
        }
        if (empty($header['authorization'])) {
            exit(json_encode([
                'code' => 401,
                'error' => '请登录管理员账号'
            ], JSON_UNESCAPED_UNICODE));
        }
        $authorization = $header['authorization'];
        $adminModel = model('Admin');
        $data = $adminModel->validationToken($authorization);
        if (!$data) {
            exit(json_encode([
                'code' => 401,
                'error' => $adminModel->getError()
            ], JSON_UNESCAPED_UNICODE));
        }
//        权限  功能做好了以后打开
//        $URL = $_SERVER['REQUEST_URI'];
//        $auth = model('auth');
//        $resauth = $auth->getUserAuth($URL);
//        if (!$resauth) {
//            exit(json_encode([
//                'code' => 500,
//                'error' => '权限不足'
//            ], JSON_UNESCAPED_UNICODE));
//        }
    }

    public function getAdminInfo() {
        $header = Request::instance()->header();
        if (empty($header['authorization'])) {
            return false;
        }
        $authorization = $header['authorization'];
        $user = Db::name('admin')->where('token', $authorization)->field('loginPwd, loginSecret', true)->find();
        header("Access-Control-Allow-Origin: *");
        if (empty($header['authorization'])) {
            exit(json_encode([
                'code' => 401,
                'error' => '请登录账号'
            ], JSON_UNESCAPED_UNICODE));
        }
        
        return $user;
    }

    public function adminLog($info,$time) {
        $request = Request::instance();
        $server = $request->server();
        Db::startTrans();
        try {
            $add = [];
            $controller = $request->controller();
            $action = $request->action();
            if (session('?userid')) {
                $add['userid'] = session('userid');
                $add['username'] = session('username');
                $add['token'] = session('token');
            } else {
                $admin = $this->getAdminInfo();
                $add['userid'] = $admin['adminId'];
                $add['username'] = $admin['loginName'];
                $add['token'] = $admin['token'];
            }
            $add['ip'] = $server['REMOTE_ADDR'];
            $add['log_url'] = $controller."/".$action;
            $add['class'] = $controller;
            $add['action'] = $action;
            $add['method'] = $_SERVER['REQUEST_METHOD'];
            // $add['log_info'] = adminGetControllerNote($controller, $action);
            $add['log_info'] = $info;
            // $add['time'] = date('Y-m-d H:i:s');
            $add['time'] = time();
            $add['input'] = '';
            $add['usetime'] = microtime(time())-$time;
            $add['from'] = "后台";
            $add['input'] = json_encode(input('post.'));
            Db::name('admin_log')->insert($add);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
}