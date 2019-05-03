<?php 
// 用户
namespace app\admin\controller\user;
use app\admin\controller\ApiCommon;
// use think\Controller;
// use think\Request;

class Index extends ApiCommon {
    public function list() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->list($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function memberList() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->memberList($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function memberRule() {
        $params =  input('post.');
        $model = model('User');
        $data = $model->memberRule($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function memberAudit() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\User.audit');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('User');
        $data = $model->memberAudit($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function memberInfo() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\User.info');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('User');
        $data = $model->memberInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function childrenMemberInfo() {
        $params =  input('post.');
        $validate = $this->validate($params, 'app\admin\validate\User.info');
        if ($validate !== true) {
            return resultArray(['error' => $validate]);
        }
        $model = model('User');
        $data = $model->childrenMemberInfo($params);
        if (!$data) {
            return resultArray(['error' => $model->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}