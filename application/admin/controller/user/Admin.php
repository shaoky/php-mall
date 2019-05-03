<?php 
// 用户
namespace app\admin\controller\user;
use app\admin\controller\ApiCommon;
// use think\Controller;
// use think\Request;

class Admin extends ApiCommon {
    public function getAdminList(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->getAdminList($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function updateAdmin(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->updateAdmin($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function addAdmin(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->addAdmin($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function deleteAdmin(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->deleteAdmin($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function infoAdmin(){
        $model = model('admin');
        $res = $model->adminInfo();
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function passwordAdmin(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->passwordAdmin($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
    public function passwordReset(){
        $params = input('post.');
        $model = model('admin');
        $res = $model->passwordReset($params);
        if (!$res){
            return resultArray(['error' => $model->getError()]);
        }else{
            return resultArray(['data'=>$res]);
        }
    }
}