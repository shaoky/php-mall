<?php 
// 用户
namespace app\comm\controller;
use think\Controller;
use think\facade\Request;


class Image extends Controller {
    public function add() {
        $file = request()->file('file');
        $info = $file->move( './upload');
        if($info){
            $params = [
                'path' => $info->getSaveName(),
            ];
            $model = model('Image');
            $data = $model->add($params);
            if (!$data) {
                return resultArray(['error' => $model->getError()]);
            }
            return resultArray(['data' => $data]);
        } else {
            return resultArray(['error' => $file->getError()]);
        }
    }
}