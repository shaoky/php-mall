<?php 
// 用户
namespace app\admin\controller\common;
use think\Controller;
// use think\Request;
// use think\facade\Env;
use app\admin\controller\ApiCommon;


class Image extends ApiCommon {
    public function add() {
        $adModel = model('Image');
        $params =  input('post.');
        $data = $adModel->addImage($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function list() {
        $adModel = model('Image');
        $params =  input('post.');
        $data = $adModel->getImageList($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
    public function delete() {
        $adModel = model('Image');
        $params =  input('post.');
        $data = $adModel->deleteImage($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }
        return resultArray(['data' => $data]);
    }
}