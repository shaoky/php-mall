<?php
namespace app\app\model;
use think\Db;
use think\Model;
use think\facade\Cache;
use app\h5\model\CommonNoLogin;
use think\helper\Time;


/**
 * @apiDefine appIndexGroup app-首页
 */

class Index extends CommonNoLogin {

    /**
     * @api {post} /app/index 1 首页
     * @apiName appIndex
     * @apiGroup appIndexGroup
     * @apiParam {Number} page = 1 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} adList 见广告表
     * @apiSuccess {Array} goodsList 见商品表
     * @apiVersion 1.0.0
     */

    public function getIndexList($request) {

        // $result['appIndex'] = Cache::store('redis')->get('appIndex');
        // if (!$result['appIndex']) {
            $result['adList'] = Db::name('ad')->where(['isOpen' => 1, 'positionId' => 3])->order('sort', 'desc')->select();
            $result['goodsList'] = Db::table('tp_goods')->where(['isOpen' => 1, 'isMemberGoods' => 1])->page($request->post('page',1), $request->post('size',20))->order('sort', 'desc')->select();
            // foreach ($result['goodsList'] as $key=>$item) {
                // $result['goodsList'][$key]['memberPrice'] = $result['goodsList'][$key]['shopPrice'];
            // }
            // Cache::store('redis')->set('appIndex', $result);
        // }

       
        return $result;
    }

    /**
     * @api {post} /app/huiming/index 2 惠民首页
     * @apiName getHuimingIndex
     * @apiGroup appIndexGroup
     * @apiParam {Number} page = 1 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} adList 广告列表
     * @apiSuccess {Array} iconList 图标列表
     * @apiSuccess {Object} cityCard 城市会员卡
     * @apiSuccess {Array} t1 图片系列1
     * @apiSuccess {Array} goodsList 推荐列表
     * @apiVersion 1.0.0
     */

    public function getHuimingIndex($request) {
        $userInfo = $this->getUserInfo();

        try {
            $result['adList'] = Db::name('ad')->where(['isOpen' => 1, 'positionId' => 4])->order('sort', 'desc')->select();
            $result['goodsList'] = Db::table('tp_goods')->where(['isOpen' => 1, 'isMemberGoods' => 0, 'isHotGoods' => 1])->page($request->post('page',1), $request->post('size',20))->order('sort', 'desc')->select();
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
            // 惠民榜单
            $result['rank'] = Db::name('goodsActivity')->alias('a')
                ->join('goodsActivityPosition b', 'a.gapId = b.gapId')
                ->join('goods c', 'a.goodsId = c.goodsId')
                ->field('a.*, b.isOpen, b.mark, c.*')
                ->where(['a.isOpen' => 1, 'b.isOpen' => 1, 'b.mark' => 'HM-IndexBillBoard'])
                ->order('gaSort', 'desc')->select();
            if ($userInfo['userType'] > 1) {
                foreach ($result['goodsList'] as &$item) {
                    $item['marketPrice'] = $item['shopPrice'];
                    $item['shopPrice'] = $item['memberPrice'];
                }
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        

        
        return $result;
    }
}
