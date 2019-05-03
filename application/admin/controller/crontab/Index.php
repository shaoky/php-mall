<?php 
// 用户
namespace app\admin\controller\crontab;
use think\Controller;

class Index extends Controller {
    public function orderSettlement() {
        $model = model('Crontab');
        $data = $model->orderSettlement();
    }
    public function orderClose() {
        $model = model('Crontab');
        $data = $model->orderClose();
    }
    public function orderAutoConfirm() {
        $model = model('Crontab');
        $data = $model->orderAutoConfirm();
    }
    public function goldMemberCount() {
        $model = model('Crontab');
        $data = $model->getGoldMemberCount();
    }
}