<?php  
namespace app\h5\model;  
use app\h5\model\CommonNoLogin;
use think\Db;
use think\facade\Cache;
use think\helper\Time;

/**
 * @apiDefine h5IndexGroup h5-首页接口
 */

class Index extends CommonNoLogin {
    /**
     * @api {post} /h5/index/data 1. 首页接口
     * @apiName indexData
     * @apiGroup h5IndexGroup
     * @apiSuccess {Array} adList 见广告表
     * @apiVersion 1.0.0
     */
    public function getIndexData() {
        $userInfo = $this->getUserInfo();
        $headerParams = $this->getHeaderParams();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        try {
            $result['adList'] = Db::name('ad')->where(['isOpen' => 1, 'positionId' => 4])->order('sort', 'desc')->select();
            // $result['goodsList'] = Db::table('tp_goods')->where(['isOpen' => 1, 'isMemberGoods' => 0, 'isHotGoods' => 1])->page($params['page'], $params['size'])->order('sort', 'desc')->select();
            // icon
            $result['iconList'] = Db::name('ad')->alias('a')
                ->join('ad_position b', 'a.positionId = b.positionId')
                ->field('a.*')
                ->where(['a.isOpen' => 1, 'b.isOpen' => 1, 'b.mark' => 'HM-indexIcon'])
                ->order('sort', 'desc')->select();
            // 城市会员卡
            $result['cityCard'] = Db::name('ad')->alias('a')
                ->join('ad_position b', 'a.positionId = b.positionId')
                ->field('a.*')
                ->where(['a.isOpen' => 1, 'b.isOpen' => 1, 'b.mark' => 'HM-indexCityCard'])
                ->order('sort', 'desc')->find();
            // 图片1
            $result['t1'] = Db::name('ad')->alias('a')
                ->join('ad_position b', 'a.positionId = b.positionId')
                ->field('a.*')
                ->where(['a.isOpen' => 1, 'b.isOpen' => 1, 'b.mark' => 'HM-indexGoods1'])
                ->order('sort', 'desc')->limit(3)->select();
            // 本周必买
            $result['weekBuy'] = Db::name('goodsActivity')->alias('a')
                ->join('goodsActivityPosition b', 'a.gapId = b.gapId')
                ->join('goods c', 'a.goodsId = c.goodsId')
                ->field('a.*, b.isOpen, b.mark, c.*')
                ->where(['a.isOpen' => 1, 'b.isOpen' => 1, 'b.mark' => 'HM-IndexWeekBuy'])
                ->order('gaSort', 'desc')->select();
            // 本周倒计时
            list($weekStart, $weekEnd) = Time::week();
            $result['weekCountdown'] = $weekEnd;
            
            // 轮播图2
            $adPosition = Db::name('ad_position')->where('mark', 'indexBanner2')->find();
            $result['bannerList2'] = Db::name('ad')->where(['isOpen' => 1, 'positionId' => $adPosition['positionId']])->order('sort', 'desc')->select();

            // 商品
            $result['goodsList'] = Db::name('goods')->where(
                ['isOpen' => 1]
            )->order('sort', 'desc')
            ->page($params['page'], $params['size'])->select();
            // 附近商家
            // $model = model('app\apphm\model\Shop');
            
            // $form = [
            //     'center' => $headerParams['location'],
            //     'size' => $params['size'],
            //     'page' => $params['page'],
            //     'filter' => ''
            // ];
            // if ($headerParams['location'] && $headerParams['from'] == 1) {
            //     $shop = $model->getCoordinate(['localtion' => $headerParams['location'], 'coordsys' => 'gps']);
            //     $form['center'] = $shop->locations;
            // }
            // $result['shopList'] = $model->getShop($form);
           
            
            

            // $result['shopList'] = Db::name('shop')->where([
            //     ['isOpen', '=', 1],
            //     ['auditStatus', '=', 1]
            // ])
            // ->field('shopId, shopName, shopSubtitle, shopSaleNum, shopStatus, shopImage')
            // ->page($params['page'], $params['size'])
            // ->select();
            // foreach($result['shopList'] as &$item) {
            //     $item['shopStatusName'] = getStatusName('shopStatus', $item['shopStatus']);
            // }  

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        
        return $result;
    }
    /**
     * @api {post} /h5/index/goods 2. 商品列表
     * @apiName indexGoods
     * @apiGroup h5IndexGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} list 见商品表
     * @apiVersion 1.0.0
     */
    public function getGoodsList($params) {
        $userInfo = $this->getUserInfo();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        try {
            $where = [
                'isOpen' => 1,
                'isMemberGoods' => 0,
                'isHotGoods' => 1
            ];
            $data['list'] = db('goods')->where($where)->page($params['page'], $params['size'])->order(['sort' => 'desc', 'saleCount' => 'desc'])->select();
            foreach($data['list'] as $key => &$item) {
                $item['goodsImage'] = $item['goodsCover'];
            }
            if ($userInfo['userType'] > 1) {
                foreach($data['list'] as $key => &$item) {
                    $item['marketPrice'] = $item['shopPrice'];
                    $item['shopPrice'] = $item['memberPrice'];
                }
            }
            
            $data['count'] = db('goods')->where($where)->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/index/store 3.获取店铺用户名称
     * @apiName indexStore
     * @apiGroup h5IndexGroup
     * @apiParam {String} userNo 用户Id
     * @apiVersion 1.0.0
     */
    public function getStoreInfo($params) {
        try {
            $data['userInfo'] = db('user')->where('userNo', $params['userNo'])->field('userName, userPhoto')->find();
            if ($data['userInfo']) {
                return $data;
            } else {
                $this->error = '获取用户信息失败';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /h5/index/share 4.分享店铺
     * @apiName getShareInfo
     * @apiGroup h5IndexGroup
     * @apiParam {String} userNo 用户Id
     * @apiVersion 1.0.0
     */
    public function getShareInfo($params) {
        try {
            if ($params['userNo'] == null) {
                $str = '';
                $url = 'https://h5.mall.shaoky.com/index';
            } else {
                $user = Db::name('user')->where('userNo', $params['userNo'])->find();
                $str = $user['userName'].'的店铺，';
                $url = 'https://h5.mall.shaoky.com/index?userNo='.$user['userNo'];
            }
            $data['info']=['title'=> $str.'低价好货推介给你','content'=>'如果让我推荐一款最适合你的产品','icon'=>'https://api.mall.shaoky.com/images/common/logo.jpg','url'=> $url];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}