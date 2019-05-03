<?php
namespace app\h5\model;
use app\h5\model\Common;
use think\Db;
/**
 * @apiDefine h5UserAddressGroup h5-用户地址表
 */

/**
 * @api {post} / 1. 用户地址表
 * @apiName userAddresss
 * @apiGroup h5UserAddressGroup
 * @apiSuccess {Number} addressId 主键
 * @apiSuccess {String} userName 用户名称
 * @apiSuccess {String} usrePhone 用户手机
 * @apiSuccess {Number} provinceId 省id
 * @apiSuccess {Number} cityId 市id
 * @apiSuccess {Number} countyId 区县id
 * @apiSuccess {Number} provinceName 省
 * @apiSuccess {Number} cityName 市
 * @apiSuccess {Number} countyName 区县
 * @apiSuccess {String} addresss 详细地址
 * @apiSuccess {Number} createTime 创建时间
 * @apiSuccess {Number} isDefault 是否默认：0不是，1 是
 * @apiVersion 1.0.0
 */
class UserAddress extends Common {
    /**
     * @api {post} /h5/user/address/add 1.1 用户地址新增
     * @apiName userAddressAdd
     * @apiGroup h5UserAddressGroup
     * @apiParam {String} userName 用户姓名
     * @apiParam {String} userPhone 用户手机
     * @apiParam {Number} provinceId 省id
     * @apiParam {Number} cityId 市id
     * @apiParam {Number} countyId 区县id
     * @apiParam {Number} address 详细地址
     * @apiParam {Number} isDefault 是否默认：0不是，1是
     * @apiVersion 1.0.0
     */
    public function add($params) {
        $params['userId'] = $this->getUserId();
        $params['createTime'] = time();
        $province = db('region')->where('id', $params['provinceId'])->find();
        $city = db('region')->where('id', $params['cityId'])->find();
        $county = db('region')->where('id', $params['countyId'])->find();
        $params['provinceName'] = $province['name'];
        $params['cityName'] = $city['name'];
        $params['countyName'] = $county['name'];

        if ($params['isDefault'] == 0) {
            try {
                $id = $this->insertGetId($params);
                return [
                    'message' => '添加成功',
                    'addressId' => $id
                ];
            } catch (\Exception $e) {
                // echo $e->getError();
                $this->error = $e->getMessage();
                return false;
            }
        }

        // 设置默认，先全部取消，再单独设置默认
        if ($params['isDefault'] == 1) {
            Db::startTrans();
            try {
                $update = [
                    'isDefault' => 0
                ];
                $this->where('userId', $params['userId'])->update($update);
                $id = $this->insertGetId($params);
                Db::commit();
                return [
                    'message' => '添加成功',
                    'addressId' => $id
                ];
            } catch (\Exception $e) {
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }

    }
    /**
     * @api {post} /h5/user/address/list 1.2 用户地址列表
     * @apiName userAddressList
     * @apiGroup h5UserAddressGroup
     * @apiParam {Number} page = 0 页码
     * @apiParam {Number} size = 20 数量
     * @apiVersion 1.0.0
     */
    public function list($params) {
        $userId = $this->getUserId();
        if (empty($params['page'])) {
            $params['page'] = 1;
        }
        if (empty($params['size'])) {
            $params['size'] = 20;
        }
        $data['list'] = $this->where('userId', $userId)->page($params['page'], $params['size'])->order('addressId', 'desc')->select();
        $data['count'] = $this->where('userId', $userId)->count();
        return $data;
    }
    /**
     * @api {post} /h5/user/address/update 1.3 用户地址更新
     * @apiName userAddressUpdate
     * @apiGroup h5UserAddressGroup
     * @apiParam {Number} addressId 地址Id
     * @apiParam {Object} object 其他参数见新增接口
     * @apiVersion 1.0.0
     */
    public function updateAddress($params) {
        $userId = $this->getUserId();
        if (empty($params['addressId'])) {
            $this->error = '请选择要更新的地址';
            return false;
        }
        $province = db('region')->where('id', $params['provinceId'])->find();
        $city = db('region')->where('id', $params['cityId'])->find();
        $county = db('region')->where('id', $params['countyId'])->find();
        $form['provinceName'] = $province['name'];
        $form['cityName'] = $city['name'];
        $form['countyName'] = $county['name'];
        $form['userName'] = $params['userName'];
        $form['userPhone'] = $params['userPhone'];
        $form['provinceId'] = $params['provinceId'];
        $form['cityId'] = $params['cityId'];
        $form['countyId'] = $params['countyId'];
        $form['address'] = $params['address'];
        $form['isDefault'] = $params['isDefault'];
        $form['addressId'] = $params['addressId'];  

        if ($params['isDefault'] == 0) {
            try {
                $data = $this->where('addressId', $params['addressId'])->update($form);
                if ($data == 1) {
                    return [
                        'message' => '更新成功'
                    ];
                } else {
                    return [
                        'message' => '更新失败'
                    ];
                }
            } catch (\Exception $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        if ($params['isDefault'] == 1) {
            Db::startTrans();
            try {
                Db::name('user_address')->where('userId', $userId)->update(['isDefault' => 0]);
                $data = Db::name('user_address')->where(['userId' => $userId, 'addressId' => $params['addressId']])->update($form);
                Db::commit();
                if ($data == 1) {
                    return [
                        'message' => '更新成功'
                    ];
                } else {
                    return [
                        'message' => '更新失败'
                    ];
                }
            } catch (\Exception $e) {
                Db::rollback();
                $this->error = $e->getMessage();
                return false;
            }
        }
    }
    /**
     * @api {post} /h5/user/address/delete 1.4 地址删除
     * @apiName userAddressDelete
     * @apiGroup h5UserAddressGroup
     * @apiParam {Number} addressId 地址Id
     * @apiVersion 1.0.0
     */
    public function deleteAddress($params) {
        $userId = $this->getUserId();
        if (empty($params['addressId'])) {
            $this->error = '请选择要删除的地址';
            return false;
        }

        $where = [
            'userId' => $userId,
            'addressId' => $params['addressId']
        ];
        try {
            $data = $this->where($where)->delete();
            if ($data == 1) {
                return [
                    'message' => '删除成功'
                ];
            } else {
                return [
                    'message' => '地址不存在'
                ];
            }
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/user/address/default/set 1.5 设置默认地址
     * @apiName userAddressSet
     * @apiGroup h5UserAddressGroup
     * @apiParam {Number} addressId 地址Id
     * @apiVersion 1.0.0
     */
    public function setDefault($params) {
        $userId = $this->getUserId();
        if (empty($params['addressId'])) {
            $this->error = '请选择要设置默认的地址';
            return false;
        }

        $where = [
            'userId' => $userId,
            'addressId' => $params['addressId']
        ];
        $update = [
            'isDefault' => 0
        ];

        Db::startTrans();
        try {
            $this->where('userId', $userId)->update($update);
            $data = $this->where($where)->update(['isDefault' => 1]);
            Db::commit();
            if ($data == 1) {
                return [
                    'message' => '修改成功'
                ];
            } else {
                return [
                    'message' => '地址不存在'
                ];
            }

        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /h5/user/address/default/get 1.6 获取默认地址
     * @apiName userAddressDefaultGet
     * @apiGroup h5UserAddressGroup
     * @apiSuccess {Object} address 见地址表，返回null，不存在默认地址
     * @apiVersion 1.0.0
     */
    public function getDefault($params) {
        $userId = $this->getUserId();
        $where = [
            'userId' => $userId,
            'isDefault' => 1
        ];
        try {
            $data = $this->where($where)->find();
            if ($data) {
                return [
                    'address' => $data
                ];
            } else {
                return [
                    'address' => null
                ];
            }

        } catch (\Exception $e) {
            Db::rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * @api {post} /h5/user/address/info 1.7 获取详细地址
     * @apiName getInfo
     * @apiGroup h5UserAddressGroup
     * @apiParam {Number} addressId 地址id
     * @apiSuccess {Object} info 见地址表
     * @apiVersion 1.0.0
     */

    public function getInfo($params) {
        if (empty($params['addressId'])) {
            $this->error = '请选择地址';
            return false;
        }

        $data['info'] = $this->where('addressId',$params['addressId'])->find();
        return $data;
    }
}
