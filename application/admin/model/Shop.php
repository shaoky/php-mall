<?php  
namespace app\admin\model;  
use app\comm\model\Qrcode;
use think\Db;
use think\Model;
use app\admin\model\Common;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @apiDefine adminAdGroup admin-店铺模块
 */

class Shop extends Common {
    // protected $autoWriteTimestamp = true;
    // protected $createTime = 'createTime';

    public function addData($params) {

        Db::startTrans();
        try {
            $params['createTime'] = time();
            $params['auditTime'] = time();
            $params['serviceStartTime'] = '9:00';
            $params['serviceEndTime'] = '23:00';
            $params['shopDiscount'] = 0.9;
            $params['isOpen'] = 0;
            $params['shopStatus'] = 0;
            $params['auditStatus'] = 1;

            $shop = Db::name('shop')->where('userId', $params['userId'])->find();
            if ($shop) {
                $this->error = '该用户已经存在店铺了';
                return;
            }
            $shopId = Db::name('shop')->insertGetId($params);
            $userLevelList = Db::name('shop_user_level')->select();
            foreach($userLevelList as $item) {
                $shopDiscount = 1 - ((1 - $params['shopDiscount']) * $item['couponRate']);
                $add = [
                    'userId' => $params['userId'],
                    'shopId' => $shopId,
                    'userType' => $item['userType'],
                    'couponRate' => $item['couponRate'],
                    'discountRate' => $shopDiscount
                ];
                Db::name('shop_discount')->insert($add);
            }
            $update['shopGatheringQcode'] = $this->makeQrcode($shopId,$params['userId']);
            Db::name('shop')->where('shopId',$shopId)->update($update);
            Db::commit();
            return '添加成功';
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

//    收款码生成
    public function makeQrcode($shopId,$userId){
//        $userInfo = $this->getInfo();
        $userInfo = Db::name('user')->where('userId',$userId)->find();
        $Qrcode = new Qrcode();
        $shopid['shopId'] = $shopId;
        $res = $Qrcode->makeQrcode(json_encode($shopid),$userInfo['userPhoto'],$_SERVER['DOCUMENT_ROOT']."/qrcode/");
        $img = json_decode($res);
        return $img->file;
    }
    public function getList($params) {
        $where = [];
        if (!empty($params['shopName'])) {
            $where[] = ['shopName', 'like', '%'.$params['shopName'].'%'];
        }
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        $data['list'] = Db::name('shop')
            ->where($where)
            ->order('shopId desc')
            ->page($params['page'], $params['size'])
            ->select();
        $data['count'] = $this->where($where)->count();
        return $data;
    }

    public function updateData($params) {
        try {
            $data = $this->where('shopId', $params['shopId'])->update($params);
            if ($data == 1) {
                return '更新成功';
            } else {
                $this->error = '更新失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function updateDiscount($params) {
        Db::startTrans();
        try {
            $data = Db::name('shop')->where('shopId', $params['shopId'])->update(['shopDiscount' => $params['shopDiscount']]);
            foreach($params['list'] as $item) {
               $shopDiscount = 1 - ((1 - $params['shopDiscount']) * $item['couponRate']);
               $update = [
                    'couponRate' => $item['couponRate'],
                    'discountRate' => $shopDiscount
                ];
                Db::name('shop_discount')->where([
                    ['shopId', '=', $params['shopId']],
                    ['userType', '=', $item['userType']]
                ])->update($update);
            }
            Db::commit();
            
            return '更新成功';
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }


    public function getInfo($params) {
        if (empty($params['shopId'])) {
            $this->error = '请传店铺id';
            return false;
        }
        try {
            $data['info'] = Db::name('shop')->where('shopId', $params['shopId'])->find();
            $data['info']['discount'] = Db::name('shop_discount')->where('shopId', $params['shopId'])->select();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function setIsOpen($params) {
        if (empty($params['shopId'])) {
            $this->error = '请选择要设置的显示/不显示的店铺';
            return false;
        }
        $map = [
            'isOpen' => $params['isOpen']
        ];
        try {
            $data = $this->where('shopId', $params['shopId'])->update($map);
            
            if ($data == 1) {
                return '操作成功';
            } else {
                return '店铺不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function setAuditStatus($params) {
        if (empty($params['shopId'])) {
            $this->error = '请选择要设置的店铺';
            return false;
        }
        $map = [
            'auditStatus' => $params['auditStatus']
        ];
        try {
            $data = $this->where('shopId', $params['shopId'])->update($map);
            
            if ($data == 1) {
                return '操作成功';
            } else {
                return '店铺不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getShopExcel() {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'name');
            $sheet->setCellValue('B1', 'address');
            $sheet->setCellValue('C1', 'x');
            $sheet->setCellValue('D1', 'y');
            $sheet->setCellValue('E1', 'telephone');
            $sheet->setCellValue('F1', 'shopId');
            
            $i = 1;

            $data = Db::name('shop')
            ->where(['auditStatus' => 1])
            ->select();
            
            foreach ($data as $item) {
                $location = explode(",",$item['shopLocation']);
                $i++;
                $sheet->setCellValue('A' . $i, $item['shopName']);
                $sheet->setCellValue('B' . $i, $item['shopAddress']);
                $sheet->setCellValue('C' . $i, $location[0]);
                $sheet->setCellValue('D' . $i, $location[1]);
                $sheet->setCellValue('E' . $i, $item['shopPhone']);
                $sheet->setCellValue('F' . $i, $item['shopId']);
            }
            $writer = new Xlsx($spreadsheet);
            $writer->save('excel/店铺列表.xlsx');
            Db::commit();
            return 'excel/店铺列表.xlsx';

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}