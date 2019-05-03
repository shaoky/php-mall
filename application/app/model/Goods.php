<?php  
namespace app\app\model;  
use think\Db;
use think\Model;
use app\h5\model\CommonNoLogin;

class Goods extends CommonNoLogin {
    public function getGoodsList() {
        $map['isOpen'] = 1;
        $result['list'] = $this->where($map)->order('sort', 'desc')->select();
        foreach ($result['list'] as $item) {
            $item['goodsImage'] = config('global.domainName').$item['goodsImage'];
            $item['memberPrice'] = $item['shopPrice'];
        }
        return $result;
    }

    public function getGoodsInfo($params) {
        // $headerParams = $this->getHaderParams();
        // $userInfo = $this->getUserInfo();
        
        // if ($headerParams['app'] == 1) {
            // $result['info'] = $this->where($params)->find();
            // $result['info']['memberPrice'] = $result['info']['shopPrice'];
            // $result['info']['goodsImage'] = config('global.domainName').$result['info']['goodsImage'];
            // return $result;
        // }
        // if ($headerParams['app'] == 2) {
            // $result['info'] = $this->where($params)->find();
            // if ($userInfo['userType'] > 1) {
            //     $result['info']['marketPrice'] = $result['info']['shopPrice'];
            //     $result['info']['shopPrice'] = $result['info']['memberPrice'];
            //     $levelInfo = $this->getUserLevel($userInfo['userType']);
            //     $result['info']['commissionMoney'] = ($result['info']['shopPrice'] - $result['info']['memberPrice']) * $levelInfo['goodsCommission'] / 100;
            // }
            
            // $result['info']['goodsImage'] = config('global.domainName').$result['info']['goodsImage'];
            // return $result;
        // }
    }

    public function getGoodsInfoShare($params) {
        $user = $this->getUserInfo();
        $headerParams = $this->getHeaderParams();
        if (empty($headerParams['app'])) {
            $headerParams['app'] = 1;
        }
        if(empty($params['goodsId'])) {
            $this->error = '请传goodsId';
            return;
        }
        try {
            $goods = Db::name('goods')->where('goodsId', $params['goodsId'])->find();
            $data['isOpen'] = 1;
            $icon;
            if ($headerParams['app'] == 1) {
                $icon = config('app.app_host').'/images/common/logo.jpg';
            }
            if ($headerParams['app'] == 2) {
                $icon = config('app.app_host').'/images/common/hm-logo.png';
            }
            if ($goods['isSeckillGoods'] == 1) {
                $data['info'] = [
                    'title' => $goods['goodsName'],
                    'content'=>$goods['goodsSubtitle'],
                    'icon'=> $goods['goodsImage'],
                    'url'=>config('app.h5_host').'/goods/seckill/info?id='.$goods['goodsId'].'&userNo='.$user['userNo']
                ];
            }
            if($headerParams['app'] == 1) {
                if ($goods['isSeckillGoods'] == 0 || $goods['isSeckillGoods'] == null) {
                    $data['info'] = [
                        'title' => $goods['goodsName'],
                        'content' => $goods['goodsSubtitle'] == '' ? $goods['goodsName'] : $goods['goodsSubtitle'],
                        'icon' => $goods['goodsImage'],
                        'url'=>config('app.h5_host').'/sp/goods/info?id='.$goods['goodsId'].'&userNo='.$user['userNo']
                    ];
                }
            }
            if($headerParams['app'] == 2) {
                if ($goods['isSeckillGoods'] == 0 || $goods['isSeckillGoods'] == null) {
                    $data['info'] = [
                        'title' => $goods['goodsName'],
                        'content' => $goods['goodsSubtitle'] == '' ? $goods['goodsName'] : $goods['goodsSubtitle'],
                        'icon'=> $goods['goodsImage'],
                        'url'=>config('app.h5_host').'/goods/info?id='.$goods['goodsId'].'&userNo='.$user['userNo']
                    ];
                }
            }
            
            
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}