<?php
namespace app\h5\model;
use app\h5\model\Common;
use think\Db;
/**
 * @apiDefine h5CartGroup h5-购物车
 */

/**
 * @api {post} / 1. 购物车表
 * @apiName cart
 * @apiGroup h5CartGroup
 * @apiSuccess {Number} cartId 主键
 * @apiSuccess {Number} goodsId 商品Id
 * @apiSuccess {String} goodsName 商品名称
 * @apiSuccess {Number} goodsNum 商品数量
 * @apiSuccess {Number} isSelected 是否选中：0不是，1 是
 * @apiVersion 1.0.0
 */
class Cart extends Common {
    /**
     * @api {post} /h5/cart/add 1.1 购物车新增
     * @apiName cartAdd
     * @apiGroup h5CartGroup
     * @apiParam {String} goodsId 商品id
     * @apiParam {String} goodsNum 商品数量
     * @apiVersion 1.0.0
     */
    public function add($params) {
        $headerParams = $this->getHeaderParams();
        $params['cartApp'] = $headerParams['app'];
        $params['userId'] = $this->getUserId();
        $params['createTime'] = time();
        $params['isSelected'] = 0;

        Db::startTrans();
        try {
            // 根据是否有sku差库存
            $goods = Db::name('goods')->where('goodsId', $params['goodsId'])->find();
            if (!empty($params['skuId'])) {
                $goodsSku = Db::name('goods_sku')->where('skuId', $params['skuId'])->find();
                $goods['goodsStock'] = $goodsSku['goodsStock'];
            }
            
            $params['goodsName'] = $goods['goodsName'];

            $cartWhere = [
                'userId' => $params['userId'],
                'goodsId' => $params['goodsId'],
                'skuId' => $params['skuId']
            ];
            $cart = Db::name('cart')->where($cartWhere)->find();
            
            if ($headerParams['app'] == 1) {
                if ($cart) {
                    Db::name('cart')->where($cartWhere)->setInc('goodsNum', $params['goodsNum']);
                } else {
                    $data = Db::name('cart')->insert($params);
                }
            }

            if ($headerParams['app'] == 2) {
                if ($cart) {
                    $app_env = config('app.app_env');
                    if ($app_env == 'production') {
                        if ($params['goodsId'] == 161) {
                            $this->error = '该商品限购最大购买1件';
                            return;
                        }
                    }
                    if ($app_env == 'test') {
                        if ($params['goodsId'] == 140) {
                            $this->error = '该商品限购最大购买1件';
                            return;
                        }
                    }
                    Db::name('cart')->where($cartWhere)->setInc('goodsNum', $params['goodsNum']);
                } else {
                    $data = Db::name('cart')->insert($params);
                }
            }

            Db::commit();
            return [
                'message' => '添加成功'
            ];
        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }

    }
    /**
     * @api {post} /h5/cart/list 1.2 购物车列表
     * @apiName cartList
     * @apiGroup h5CartGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function list($params) {
        $headerParams = $this->getHeaderParams();
        if (empty($headerParams['app'])) {
            $headerParams['app'] = 1;
        }
        $user = $this->getUserInfo();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        $data['list'] = $this->alias('a')
        ->join('goods b', 'a.goodsId = b.goodsId')
        ->field('a.*, b.goodsImage, b.goodsStock, b.marketPrice, b.shopPrice, b.memberPrice')
        ->where(['userId' => $user['userId'], 'b.isOpen' => 1, 'a.cartApp' => $headerParams['app']])
        ->page($params['page'], $params['size'])
        ->order('cartId', 'desc')
        ->select();

        foreach($data['list'] as &$item) {
            if ($item['skuId']) {
                $goodsSku = Db::name('goods_sku')->where('skuId', $item['skuId'])->find();
                $item['goodsStock'] = $goodsSku['goodsStock'];
                $goodsSku['skuSpec'] = json_decode($goodsSku['skuSpec']);
                $item['skuSpec'] = implode("，", $goodsSku['skuSpec']);
                $item['shopPrice'] = $goodsSku['shopPrice'];
                $item['memberPrice'] = $goodsSku['memberPrice'];
                if ($user['userType'] == 1) {
                    $item['goodsPrice'] = $goodsSku['shopPrice'];
                } else {
                    $item['goodsPrice'] = $goodsSku['memberPrice'];
                }
            } else {
                if ($user['userType'] == 1) {
                    $item['goodsPrice'] = $item['shopPrice'];
                } else {
                    $item['goodsPrice'] = $item['memberPrice'];
                }
            }
            
        }
        return $data;
    }
    /**
     * @api {post} /h5/cart/update 1.3 购物车更新
     * @apiName cartUpdate
     * @apiGroup h5CartGroup
     * @apiParam {Number} cartId 购物车id
     * @apiParam {Number} goodsNum 商品数量
     * @apiParam {Number} isSelected 是否选中
     * @apiVersion 1.0.0
     */
    public function updateCart($params) {
        $headerParams = $this->getHeaderParams();
        if (empty($headerParams['app'])) {
            $headerParams['app'] = 1;
        }
        $userId = $this->getUserId();
        try {
            $where = [
                'userId' => $userId,
                'cartId' => $params['cartId']
            ];

            $cart = $this->where($where)->find();
            if ($cart['skuId']) {
                $goodsSku = Db::name('goods_sku')->where('skuId', $cart['skuId'])->find();
                if ($params['goodsNum'] > $goodsSku['goodsStock']){
                    $this->error = '库存不足';
                    return;
                }
            } else {
                $good = Db::table('tp_goods')->where('goodsId',$cart['goodsId'])->find();
                if ($params['goodsNum'] > $good['goodsStock']){
                    $this->error = '库存不足';
                    return;
                }
            }
            

            if ($headerParams['app'] == 2) {
                $app_env = config('app.app_env');
                if ($app_env == 'production') {
                    if ($cart['goodsId'] == 161 && $params['goodsNum'] > 1) {
                        $this->error = '该商品限购最大购买1件';
                        return;
                    }
                }
                if ($app_env == 'test') {
                    if ($cart['goodsId'] == 140 && $params['goodsNum'] > 1) {
                        $this->error = '该商品限购最大购买1件';
                        return;
                    }
                }
            }

            $data = $this->where($where)->update(['goodsNum' => $params['goodsNum'], 'isSelected' => $params['isSelected']]);
            if ($data == 1) {
                return [
                    'message' => '更新成功'
                ];
            } else {
                $this->error = '更新失败';
                return;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/cart/delete 1.4 购物车删除
     * @apiName cartDelete
     * @apiGroup h5CartGroup
     * @apiParam {Number} cartId 购物车Id
     * @apiVersion 1.0.0
     */
    public function deleteCart($params) {
        $userId = $this->getUserId();
        if (empty($params['cartId'])) {
            $this->error = '请选择要删除的购物车商品';
            return false;
        }

        $where = [
            'userId' => $userId,
            'cartId' => $params['cartId']
        ];
        try {
            $data = $this->where($where)->delete();
            if ($data == 1) {
                return [
                    'message' => '删除成功'
                ];
            } else {
                $this->error = '购物车商品不存在';
                return;
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /h5/cart/select 1.5 购物车全选/不选
     * @apiName select
     * @apiGroup h5CartGroup
     * @apiParam {Number} isSelected 是否全选：0全不选,1全选
     * @apiVersion 1.0.0
     */

    public function select($request)
    {
        $userId = $this->getUserId();
        if (!$request->has('isSelected')) {
            $this->error = 'isSelected参数为空';
            return false;
        }elseif($request->isSelected == 0 || $request->isSelected == 1){
            $where = [
                'userId' => $userId,
            ];
            $data = $this->where($where)->update(['isSelected' => $request->isSelected]);
            if ($data) {
                return [
                    'message' => '更新成功'
                ];
            } else {
                $this->error = '更新失败';
                return;
            }
        }
        $this->error = 'isSelected只能为1或0';
        return false;

    }

}
