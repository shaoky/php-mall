<?php  
namespace app\h5\model;  
use app\h5\model\CommonNoLogin;
use think\Db;
use app\admin\model\WebConfig;
/**
 * @apiDefine h5GoodsGroup h5-商品模块
 */

class Goods extends CommonNoLogin {
    /**
     * @api {post} /h5/goods/list 1. 全部列表
     * @apiName goodsList
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiParam {String} goodsName 商品名称
     * @apiParam {Number} [sales = 0] 销量1降序，2升序
     * @apiParam {Number} [price = 0] 价格1降序，2升序
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
        if (empty($params['goodsName'])) {
            $params['goodsName'] = '';
        }

        
        // 默认
        $order = [
            'sort' => 'desc'
        ];

        
        // 销量
        if (empty($params['sales'])) {
            // $order = [
            //     'saleCount' => 'desc'
            // ];
        } else {
            if ((int)$params['sales'] == 1) {
                $order = [
                    'saleCount' => 'desc'
                ];
            }
            if ((int)$params['sales'] == 2) {
                $order = [
                    'saleCount' => 'acs'
                ];
            }
        }

        // 价格
        if (empty($params['price'])) {
            // $order = [
            //     'saleCount' => 'desc'
            // ];
        } else {
            if ((int)$params['price'] == 1) {
                $order = [
                    'shopPrice' => 'desc'
                ];
            }
            if ((int)$params['price'] == 2) {
                $order = [
                    'shopPrice' => 'acs'
                ];
            }
        }
        
        try {
            $where = [
                'isOpen' => 1,
                'isMemberGoods' => 0
            ];
            $data['list'] = db('goods')
                ->where($where)
                ->whereLike('goodsName', '%'.$params['goodsName'].'%')
                ->page($params['page'], 20)
                ->order($order)
                ->select();
            if ($userInfo['userType'] > 1) {
                foreach($data['list'] as &$item) {
                    $item['marketPrice'] = $item['shopPrice'];
                    $item['shopPrice'] = $item['memberPrice'];
                }
            }
            
            $data['count'] = db('goods')
                ->where($where)
                ->whereLike('goodsName', '%'.$params['goodsName'].'%')
                ->count();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
     /**
     * @api {post} /h5/goods/info 2. 商品详情
     * @apiName goodsInfo
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} goodsId 商品id
     * @apiSuccess {Object} info 见商品表
     * @apiSuccess {Array} .goodsBannerList 轮播图
     * @apiSuccess {Array} .goodsDetailList 详情图
     * @apiSuccess {Array} .goodsSkuList sku的集合
     * @apiSuccess {Array} .goodsSpec 所有规格的集合，循环这条，去goodsSkuList里匹配符合的数据
     * @apiSuccess {Number} .commissionMoney 推广费，登录并且是会员以上才显示这行
     * @apiVersion 1.0.0
     */
    public function getGoodsInfo($params) {
        if (empty($params['goodsId'])) {
            $this->error = '请传商品id';
            return false;
        }
        $user = $this->getUserInfo();

        $app_env = config('app.app_env');
        $isUse = true;
        if ($app_env == 'production') {
            if ($params['goodsId'] == 161) {
                $isUse = false;
            }
        }
        if ($app_env == 'test') {
            if ($params['goodsId'] == 140) {
                $isUse = false;
            }
        }
        try {
            $where = [
                'goodsId' => $params['goodsId'],
                'isOpen' => 1
            ];
            $data['info'] = db('goods')->where($where)->find();
            if (!$data['info']) {
                $this->error = '没有相关产品';
                return;
            }
            // $data['info']['memberPrice'] = $data['info']['shopPrice'];
            // 如果是抢购商品，获取相关的信息
            if ($data['info']['isSeckillGoods'] == 1 && $isUse) {
                $data['seckill'] = Db::name('goodsSeckill')->where('goodsId', $data['info']['goodsId'])->find();
            }
            $list = db('goodsImage')->where('goodsId', $params['goodsId'])->select();
            $goodsBannerList = [];
            $goodsDetailList = [];
            foreach($list as $item) {
                if ($item['type'] == 1) {
                    $goodsBannerList[]= $item;
                }
                if ($item['type'] == 2) {
                    $goodsDetailList[] = $item;
                }
            }
            $data['info']['goodsBannerList'] = $goodsBannerList;
            $data['info']['goodsDetailList'] = $goodsDetailList;
            $data['info']['goodsSpec'] = json_decode($data['info']['goodsSpec']);
            if ($data['info']['goodsSpec']) {
                $skuList = Db::name('goods_sku')->where('goodsId', $data['info']['goodsId'])->select();
                foreach($skuList as &$item) {
                    $item['skuSpec'] = json_decode($item['skuSpec']);
                    $data['info']['goodsSkuList'][] = $item;
                }
                $data['info']['shopPrice'] = $skuList[0]['shopPrice'];
                if ($user) {
                    if ($user['userType'] > 1) {
                        $data['info']['memberPrice'] = $skuList[0]['memberPrice'];
                    }
                }
                
            }
            // 商品属性，如果没有设置值，就不显示
            $goodsAttr = json_decode($data['info']['goodsAttr']);
            $data['info']['goodsAttr'] = [];
            foreach($goodsAttr as $item) {
                if (!empty($item->value)) {
                    $data['info']['goodsAttr'][] = $item;
                }
            }
            if ($user) {
                if ($user['userType'] > 1) {
                    $levelInfo = $this->getUserLevel($user['userType']);
                    $data['info']['marketPrice'] = $data['info']['shopPrice'];
                    $data['info']['commissionMoney'] = ($data['info']['shopPrice'] - $data['info']['memberPrice']) * $levelInfo['goodsCommission'] / 100;
                    
                    $data['info']['commissionMoney'] = (string)$data['info']['commissionMoney'];
                    $app_env = config('app.app_env');
                    if ($app_env == 'production') {
                        if ($params['goodsId'] == 161) {
                            $data['info']['commissionMoney'] = 9.9;
                        }
                    }
                    if ($app_env == 'test') {
                        if ($params['goodsId'] == 140) {
                            $data['info']['commissionMoney'] = 9.9;
                        }
                    }

                    $data['info']['shopPrice'] = $data['info']['memberPrice'];
                    if ($data['info']['isSeckillGoods'] == 1 && $isUse) {
                        $data['seckill']['minBuy'] = $data['seckill']['memberMinBuy'];
                        $data['seckill']['maxBuy'] = $data['seckill']['maxBuy'];
                    }
                }
            }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/goods/member/list 3. 会员包商品列表
     * @apiName memberGoodsList
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiSuccess {Array} list 见商品表
     * @apiVersion 1.0.0
     */
    public function getMemberGoodsList($params) {
        $user = $this->getUserInfo();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        try {
            $where = [
                'isOpen' => 1,
                'isMemberGoods' => 1,
            ];
            $data['list'] = db('goods')->where($where)->page($params['page'], $params['size'])->order('sort', 'desc')->select();
            $data['count'] = db('goods')->where($where)->page($params['page'], $params['size'])->order('goodsId', 'desc')->count();
            $data['userType'] = 1;
            if ($user) {
                $data['userType'] = $user['userType'];
            }
            
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /app/goods/share 4. 商品分享
     * @apiName shopShare
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} goodsId 商品id
     * @apiSuccess {Number} isOpen 是否开启，0不，1是
     * @apiSuccess {Object} info
     * @apiSuccess {String} .title 标题
     * @apiSuccess {String} .content 内容
     * @apiSuccess {String} .icon 图标
     * @apiSuccess {String} .url 分享链接
     * @apiVersion 1.0.0
     */

    /**
     * @api {post} /app/goods/series/list 5. 商品合集系列
     * @apiName getGoodsSeriesList
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} goodsId 商品id
     * @apiSuccess {Object} info 合集详情
     * @apiSuccess {String} .seriesTitle 合集标题
     * @apiSuccess {String} .seriesContent 合集内容
     * @apiSuccess {Array} list 商品列表
     * @apiVersion 1.0.0
     */
    public function getGoodsSeriesList ($params) {
        try {
            $data['info'] = Db::name('goods')->where('goodsId', $params['goodsId'])->field('seriesTitle, seriesContent,goodsCover')->find();
            $data['list'] = Db::name('goods_series')->alias('a')
                ->join('goods b', 'a.goodsChildrenId = b.goodsId')
                ->field('b.*')
                ->where('a.goodsId', $params['goodsId'])
                ->order('seriesSort', 'desc')
                ->select();
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

     /**
     * {post} /app/goods/type/list 6. 商品分类
     * @apiName getGoodsTypeList
     * @apiGroup h5GoodsGroup
     * @apiParam {Number} goodsId 商品id
     * @apiSuccess {Object} info 合集详情
     * @apiSuccess {String} .seriesTitle 合集标题
     * @apiSuccess {String} .seriesContent 合集内容
     * @apiSuccess {Array} list 商品列表
     * @apiVersion 1.0.0
     */
    public function getGoodsTypeList ($params) {
        // if (isset($params['goodsClassId'])) {
        //     $where['parentId'] = $params['goodsClassId'];
        // } else {
        //     $where['parentId'] = 0;
        // }

        $where['isOpen'] = 1;
        try {
            $data['list'] = Db::name('goods_type')->where($where)->order('sort', 'desc')->select();
            $this->getGoodsTypeListTree($data['list']);
            // foreach($data['list'] as $key=>$item) {
            //     $where1['parentId'] = $item['goodsClassId'];
            //     $data['list'][$key]['children'] = Db::name('goods_type')->where($where1)->select();
            // }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getGoodsTypeListTree($array, $pid = 0, $level = 0) {
        //声明静态数组,避免递归调用时,多次声明导致数组覆盖
        $list = [];
        // $columnList = Db::name('column')->where('columnPid', $pid)->select();
        foreach ($array as $key => $value){
            echo $pid;
            //第一次遍历,找到父节点为根节点的节点 也就是pid=0的节点
            if ($value['parentId'] == $pid){
                //父节点为根节点的节点,级别为0，也就是第一级
                $value['level'] = $level;
                //把数组放到list中
                $value['children'] = $this->getGoodsTypeListTree($array, $value['goodsClassId'], $level+1);
                $list[] = $value;
                //把这个节点从数组中移除,减少后续递归消耗
                unset($array[$key]);
                //开始递归,查找父ID为该节点ID的节点,级别则为原级别+1
            }
        }
        return $list;
    }

    /**
     * @api {post} /app/goods/search/list 6. 热门搜索
     * @apiName getGoodsSearchList
     * @apiSuccess {Array} list 列表
     * @apiSuccess {Number} .sort 排序
     * @apiSuccess {String} .keyword 关键词
     * @apiGroup h5GoodsGroup
     * @apiVersion 1.0.0
     */
    public function getGoodsSearchList ($params) {
        try {
            $data['list'][] = [
                'sort' => 10,
                'keyword' => '葡萄酒'
            ];
            $data['list'][] = [
                'sort' => 9,
                'keyword' => '白酒'
            ];
            $data['list'][] = [
                'sort' => 8,
                'keyword' => '五粮液'
            ];
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /app/goods/Like/list 7. 猜你喜欢
     * @apiName getGoodsLikeList
     * @apiSuccess {Array} list 商品列表
     * @apiGroup h5GoodsGroup
     * @apiVersion 1.0.0
     */
    public function getGoodsLikeList ($params) {
        try {
            $goodsList = Db::name('goods')->where([
                ['isOpen', '=', 1],
                ['isMemberGoods', '=', 0]
            ])->select();
            shuffle($goodsList);
            $data['list'] = array_slice($goodsList, 0 , 6);
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
}