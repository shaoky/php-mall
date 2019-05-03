<?php  
namespace app\comm\model;  
use think\Db;
use think\Model;
/**
 * @apiDefine commonGroup 通用接口
 */

/**
 * @api {post} /common/region/all 2.1 获取地区
 * @apiName regionAll
 * @apiGroup commonGroup
 * @apiParam {Number} [id] 地区编号
 * @apiVersion 1.0.0
 */
class Region extends Model {
    public function getRegionAll($params) {
        // 根据id获取列表
        if (!empty($params['id'])) {
            $result = db('region')->select();
            $isProvince = false;
            $isCity = false;
            $list = [];

            if (strpos($params['id'], '000000') !== false) {
                $isProvince = true;
            } else {
                $isCity = true;
            }
            
            // 如果是全国，获取省列表
            if (strpos($params['id'], '00000000') !== false) {
                $id = substr($params['id'], 0, 6);
                foreach($result as $key=>$item) {
                    $id1 = substr($item['id'], 2);
                    if ($id == $id1) {
                        $list[] = $item;
                    }
                }
                return [
                    'region' => $list
                ];
            }

            // 如果是省id，获取市列表
            if ($isProvince) {
                $id = substr($params['id'], 0, 2);
                foreach($result as $key=>$item) {
                    $id1 = substr($item['id'], 0, 2);
                    if ($id == $id1) {
                        if (strpos($item['id'], '0000') !== false && strpos($item['id'], '000000') == false) {
                            $list[] = $item;
                        }
                    }
                }
                return [
                    'region' => $list
                ];
            }
            
            // 如果是市id，获取市区列表
            if ($isCity) {
                $id = substr($params['id'], 0, 4);
                foreach($result as $key=>$item) {
                    $id1 = substr($item['id'], 0, 4);
                    if ($id == $id1) {
                        if (strpos($item['id'], '00') !== false && strpos($item['id'], '0000') == false) {
                            $list[] = $item;
                        }
                    }
                }
                return [
                    'region' => $list
                ];
            }
        }

        // 获取全部
        try {
            $result = db('region')->select();
            $index = 0;
            $index1 = 0;
            foreach($result as $key=>$item) {
                // 省
                if (strpos($item['id'], '000000') !== false) {
                    $list[$index] = $item;
                    $index++;
                    $index1 = 0;
                } else {
                    // 市
                    if (strpos($item['id'], '0000') !== false) {
                        $list[$index-1]['children'][] = $item;
                        $index1++;
                    } else {
                        // 区县
                        if (strpos($item['id'], '00') !== false) {
                            $list[$index-1]['children'][$index1-1]['children'][] = $item;
                        }
                    }
                }
            }
            $data = [
                'id' => '00000000',
                'name' => '中国',
                'chindren' => $list
            ];
            return [
                'region'=> $data
            ];
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * @api {post} /common/region/city 2.2 获取城市列表
     * @apiName getCityAll
     * @apiGroup commonGroup
     * @apiSuccess {Array} list
     * @apiVersion 1.0.0
     */
    public function getCityAll() {
        try {
            $result['list'] = Db::name('region')->where([
                ['id', 'like', '%'.'0000'],
                ['name', '<>', '县'],
                ['name', '<>', '市辖区'],
                ['name', 'NOT LIKE', '%'.'省'.'%'],
                ['id', 'NOT LIKE', '%'.'000000'],
                ['id', '<>', '65900000']
            ])->select();
            // $result['list'] = [];
            // foreach($data as $item) {
            //     if (strpos($item['id'], '000000') === false) {
            //         $result['list'][] = $item;
            //     }
            // }
            $result['list'] = $this->groupByInitials($result['list'], 'pinyin');
            return $result;

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }
    /**
     * 二维数组根据首字母分组排序
     * @param  array  $data      二维数组
     * @param  string $targetKey 首字母的键名
     * @return array            根据首字母关联的二维数组
     */
    public function groupByInitials(array $data, $targetKey = 'name') {
        $data = array_map(function ($item) use ($targetKey) {
            return array_merge($item, [
                'initials' => $this->getInitials($item[$targetKey]),
            ]);
        }, $data);
        $data = $this->sortInitials($data);
        return $data;
    }

    /**
     * 按字母排序
     * @param  array  $data
     * @return array
     */
    public function sortInitials(array $data)
    {
        $sortData = [];
        foreach ($data as $key => $value) {
            $sortData[$value['initials']][] = $value;
            // $sortData[$value['initials']][]['children'] = $value;
        }
        ksort($sortData);
        return $sortData;
    }

     /**
     * 获取首字母
     * @param  string $str 汉字字符串
     * @return string 首字母
     */
    public function getInitials($str)
    {
        if (empty($str)) {return '';}
        $fchar = ord($str{0});
        if ($fchar >= ord('A') && $fchar <= ord('z')) {
            return strtoupper($str{0});
        }

        $s1  = iconv('UTF-8', 'gb2312', $str);
        $s2  = iconv('gb2312', 'UTF-8', $s1);
        $s   = $s2 == $str ? $s1 : $str;
        $asc = ord($s{0}) * 256 + ord($s{1}) - 65536;
        if ($asc >= -20319 && $asc <= -20284) {
            return 'A';
        }

        if ($asc >= -20283 && $asc <= -19776) {
            return 'B';
        }

        if ($asc >= -19775 && $asc <= -19219) {
            return 'C';
        }

        if ($asc >= -19218 && $asc <= -18711) {
            return 'D';
        }

        if ($asc >= -18710 && $asc <= -18527) {
            return 'E';
        }

        if ($asc >= -18526 && $asc <= -18240) {
            return 'F';
        }

        if ($asc >= -18239 && $asc <= -17923) {
            return 'G';
        }

        if ($asc >= -17922 && $asc <= -17418) {
            return 'H';
        }

        if ($asc >= -17417 && $asc <= -16475) {
            return 'J';
        }

        if ($asc >= -16474 && $asc <= -16213) {
            return 'K';
        }

        if ($asc >= -16212 && $asc <= -15641) {
            return 'L';
        }

        if ($asc >= -15640 && $asc <= -15166) {
            return 'M';
        }

        if ($asc >= -15165 && $asc <= -14923) {
            return 'N';
        }

        if ($asc >= -14922 && $asc <= -14915) {
            return 'O';
        }

        if ($asc >= -14914 && $asc <= -14631) {
            return 'P';
        }

        if ($asc >= -14630 && $asc <= -14150) {
            return 'Q';
        }

        if ($asc >= -14149 && $asc <= -14091) {
            return 'R';
        }

        if ($asc >= -14090 && $asc <= -13319) {
            return 'S';
        }

        if ($asc >= -13318 && $asc <= -12839) {
            return 'T';
        }

        if ($asc >= -12838 && $asc <= -12557) {
            return 'W';
        }

        if ($asc >= -12556 && $asc <= -11848) {
            return 'X';
        }

        if ($asc >= -11847 && $asc <= -11056) {
            return 'Y';
        }

        if ($asc >= -11055 && $asc <= -10247) {
            return 'Z';
        }

        return null;
    }
}