<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;

class ShopUserLevel extends Common {

    public function updateData($params) {
        try {
            foreach($params as $item) {
                Db::name('shop_user_level')->where([
                    ['userType', '=', $item['userType']]
                ])->update(['couponRate' => $item['couponRate']]);
            }
            return '更新成功';
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}