<?php  
namespace app\admin\model;  
use think\Db;
use think\Model;
use app\admin\model\Common;
/**
 * @apiDefine adminGoodsGroup admin-商品模块
 */


class Goods extends Common {
    protected $autoWriteTimestamp = true;
    protected $createTime = 'createTime';
    
    public function addGoods($params) {
        if (empty($params['sort'])) {
            $params['sort'] = 100;
        }
        $goodsBannerList = $params['goodsBannerList'];
        $goodsDetailList = $params['goodsDetailList'];
        $goodsSkuList = $params['goodsSkuList'];
        foreach ($params as $key=>$item) {
            if ($key == 'goodsBannerList' || $key == 'goodsDetailList' || $key == 'goodsSkuList') {
                unset($params[$key]);
            }
        }
        $params['createTime'] = time();
        Db::startTrans();
        try {
            $goodsId = $this->insertGetId($params);
            foreach ($goodsBannerList as $item) {
                $arr = [
                    'type' => 1,
                    'goodsId' => $goodsId,
                    'imageUrl' => $item,
                    'sort' => 100,
                    'createTime' => time()
                ];
                $form[] = $arr;
            }
           
            foreach ($goodsDetailList as $item) {
                $arr = [
                    'type' => 2,
                    'goodsId' => $goodsId,
                    'imageUrl' => $item,
                    'sort' => 100,
                    'createTime' => time()
                ];
                $form[] = $arr;
            }
            $image = Db::name('goods_image')->insertAll($form);

            // sku
            if (count($goodsSkuList) > 0) {
                foreach($goodsSkuList as $item) {
                    $arr1 = [
                        'goodsId' => $goodsId,
                        'goodsStock' => $item['goodsStock'],
                        'goodsStockWarn' => $item['goodsStockWarn'],
                        'memberPrice' => $item['memberPrice'],
                        'shopPrice' => $item['shopPrice'],
                        'skuSpec' => $item['textValue'],
                        'createTime' => time(),
                    ];
                    $form1[] = $arr1;
                }
                Db::name('goods_sku')->insertAll($form1);
            }
            
            Db::commit();
            return '添加成功';
        } catch (\Exception $e) {
            Db::rollback();
            // echo $e->getError();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getList($params) {
        if (empty($params['goodsName'])) {
            $params['goodsName'] = '';
        }
        $where = [];
        if (isset($params['isOpen'])) {
            $where[] = ['a.isOpen', '=', $params['isOpen']];
        }
        if (isset($params['isMemberGoods'])) {
            $where[] = ['a.isMemberGoods', '=', $params['isMemberGoods']];
        }
        if (isset($params['isSeckillGoods'])) {
            $where[] = ['a.isSeckillGoods', '=', $params['isSeckillGoods']];
        }
        if (isset($params['goodsStockStatus'])) {
            // 待售罄，查询小于50的库存
            if ($params['goodsStockStatus'] == 1) {
                $where[] = ['a.goodsStock', 'between',[1, 50]];
            }
            if ($params['goodsStockStatus'] == 2) {
                $where[] = ['a.goodsStock', '=', 0];
            }
        }
        $data['list'] = $this->alias('a')
            ->join('goodsType b', 'a.goodsClassId = b.goodsClassId')
            ->field('a.*, b.goodsClassName')
            ->whereLike('a.goodsName', '%'.$params['goodsName'].'%')
            ->where($where)
            ->page($params['page'], $params['size'])
            ->order('sort', 'desc')
            ->select();
        foreach($data['list'] as $item) {
            $item['isOpen'] = getStatusName('isOpen', $item['isOpen']);
        }
        $data['count'] = Db::name('goods')->alias('a')
        ->join('goodsType b', 'a.goodsClassId = b.goodsClassId')
        ->field('a.*, b.goodsClassName')
        ->whereLike('a.goodsName', '%'.$params['goodsName'].'%')
        ->where($where)->count();
        return $data;
    }

    public function updateGoods($params) {
        $map = [
            'goodsName' => $params['goodsName'],
            'goodsAttr' => $params['goodsAttr'],
            'goodsSubtitle' => $params['goodsSubtitle'],
            'goodsImage' => $params['goodsImage'],
            'goodsVideo' => $params['goodsVideo'],
            'goodsThums' => $params['goodsThums'],
            'goodsCover' => $params['goodsCover'],
            'marketPrice' => $params['marketPrice'],
            'shopPrice' => $params['shopPrice'],
            'memberPrice' => $params['memberPrice'],
            'goodsStock' => $params['goodsStock'],
            'isFreeShipping' => $params['isFreeShipping'],
            'isGoodsSku' => $params['isGoodsSku'],
            'isMemberGoods' => $params['isMemberGoods'],
            'isHotGoods' => $params['isHotGoods'],
            'goodsAttrUnit' => $params['goodsAttrUnit'],
            'goodsAttrML' => $params['goodsAttrML'],
            'goodsAttrNumber' => $params['goodsAttrNumber'],
            'saleCount' => $params['saleCount'],
            'isOpenSeries' => $params['isOpenSeries'],
            'goodsBrandId' => $params['goodsBrandId'],
            'goodsDesc' => $params['goodsDesc'],
            'goodsWeightUnit' => $params['goodsWeightUnit'],
            'goodsStockWarn' => $params['goodsStockWarn'],
            'isMemberPrice' => $params['isMemberPrice'],
            'goodsMinimum' => $params['goodsMinimum'],
            'goodsMaximun' => $params['goodsMaximun'],
            'goodsWeight' => $params['goodsWeight'],
            'seriesTitle' => $params['seriesTitle'],
            'seriesContent' => $params['seriesContent'],
            'sort' => $params['sort'],
            'goodsSource' => $params['goodsSource'],
            'goodsSourceUrl' => $params['goodsSourceUrl'],
            'isSeckillGoods' => $params['isSeckillGoods'],
            'goodsSpec' => $params['goodsSpec'],
            'goodsAttr' => $params['goodsAttr'],
            'goodsSpecList' => $params['goodsSpecList']
        ];
        Db::startTrans();
        try {
            $data = $this->where('goodsId', $params['goodsId'])->update($map);
            Db::name('goods_sku')->where('goodsId', $params['goodsId'])->delete();
            foreach($params['goodsSkuList'] as $item) {
                $arr = [
                    'goodsId' => $params['goodsId'],
                    'goodsStock' => $item['goodsStock'],
                    'goodsStockWarn' => $item['goodsStockWarn'],
                    'memberPrice' => $item['memberPrice'],
                    'shopPrice' => $item['shopPrice'],
                    'skuSpec' => $item['textValue'],
                    'createTime' => time(),
                ];
                $form1[] = $arr;
            }
            if (count($params['goodsSkuList'])>0) {
                Db::name('goods_sku')->insertAll($form1);
            }
            
            Db::commit();

            return '更新成功';
           
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function deleteGoods($params) {
        if (empty($params['goodsId'])) {
            $this->error = '请选择要删除的商品';
            return false;
        }
        try {
            $data = $this->where('goodsId', $params['goodsId'])->delete();
            if ($data == 1) {
                return '删除成功';
            } else {
                return '广告不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function setIsOpen($params) {
        if (empty($params['goodsId'])) {
            $this->error = '请选择要设置的上架/下架的商品';
            return false;
        }
        $map = [
            'isOpen' => $params['isOpen']
        ];
        try {
            $data = $this->where('goodsId', $params['goodsId'])->update($map);
            
            if ($data == 1) {
                return '操作成功';
            } else {
                return '商品不存在';
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getInfo($params) {
        if (empty($params['goodsId'])) {
            $this->error = '商品id出错了';
            return false;
        }
        try {
            $data['info'] = Db::name('goods')->alias('a')
                ->leftJoin('goods_brand b', 'a.goodsBrandId = b.brandId')
                ->field('a.*, b.brandName')
                ->where('goodsId', $params['goodsId'])->find();
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
            $data['info']['goodsSkuList'] = Db::name('goods_sku')->where('goodsId', $params['goodsId'])->select();
            $data['info']['goodsSkuList1'] = [];
            foreach($data['info']['goodsSkuList'] as &$item) {
                $skuSpec = json_decode($item['skuSpec']);
                $item1 = $item;
                $item1['text'] = $skuSpec;
                $data['info']['goodsSkuList1'][] = $item1;
                foreach($skuSpec as &$item1) {
                    $item['text'][] = [
                        'value' => $item1
                    ];
                }
                
            }
            return $data;
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    public function getAttrList($params) {
        $where = [];
        if(!empty($params['isOpen'])) {
            $where[] = ['a.isOpen', '=', $params['isOpen']];
        }
        if(!empty($params['goodsClassId'])) {
            $where[] = ['a.goodsClassId', '=', $params['goodsClassId']];
        }
        if(!empty($params['isSelectSuperior'])) {
            $isSelectSuperior = $params['isSelectSuperior'];
        } else {
            $isSelectSuperior = 0;
        }
        Db::startTrans();
        try {
            $data['list'] = Db::name('goods_attr')->alias('a')
            ->where($where)
            ->join('goods_type b', 'a.goodsClassId = b.goodsClassId')
            ->field('a.*, b.goodsClassName')
            ->order('sort', 'desc')->select();
            foreach($data['list'] as $key => $item) {
                $data['list'][$key]['attrTypeName'] = getStatusName('attrType', $item['attrType']);
                $data['list'][$key]['isOpen'] = getStatusName('isOpen', $item['isOpen']);
            }
            if (count($data['list']) == 0) {
                $goodsType = Db::name('goods_type')->where('goodsClassId', $params['goodsClassId'])->find();
                $data['list'] = Db::name('goods_attr')->alias('a')
                ->where(['a.isOpen' => 1, 'a.goodsClassId' => $goodsType['parentId']])
                ->join('goods_type b', 'a.goodsClassId = b.goodsClassId')
                ->field('a.*, b.goodsClassName')
                ->order('sort', 'desc')->select();
                foreach($data['list'] as $key => $item) {
                    $data['list'][$key]['attrTypeName'] = getStatusName('attrType', $item['attrType']);
                    $data['list'][$key]['isOpen'] = getStatusName('isOpen', $item['isOpen']);
                }
            }
            Db::commit();
            return $data;
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }

        return $data;
    }
}