<?php
// 用户
namespace app\app\controller;
use think\Request;
use think\Controller;
class Index extends Controller {
    public function index(Request $request) {
        $adModel = model('Index');
        $data = $adModel->getIndexList($request);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function hmShare() {
        $params = input('post.');
        $adModel = model('Index');
        $data = $adModel->getHmShare($params);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }

    public function huimingIndex(Request $request) {
        $adModel = model('Index');
        $data = $adModel->getHuimingIndex($request);
        if (!$data) {
            return resultArray(['error' => $adModel->getError()]);
        }

        return resultArray(['data' => $data]);
    }
}
