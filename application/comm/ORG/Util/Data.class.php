<?php

namespace Common\ORG\Util;

/**
 * 数据处理类
 * Class Data
 * @package extend\util
 */
final class Data
{

    /**
     * 获得树状数据
     * @param array $data 数据
     * @param string $title 字段名
     * @param string $fieldPri 主键id
     * @param string $fieldPid 父id
     * @return array
     */
    static public function tree($data, $title, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        if (!is_array($data) || empty($data))
            return array();

        //设置主键为$fieldPri
        $arr = array();
        foreach ($data as $d) {
            $d['text'] = $d[$title];
            $arr[$d[$fieldPri]] = $d;
        }
        return self::generateTree($arr, $fieldPri, $fieldPid);
    }

    /**
     * 生成树
     * @param $items
     * @param string $fieldPri
     * @param string $fieldPid
     * @return array
     */
    static public function generateTree($items, $fieldPri = 'cid', $fieldPid = 'pid')
    {
        $tree = array();
        foreach ($items as $item) {
            if (isset($items[$item[$fieldPid]])) {
                $items[$item[$fieldPid]]['children'][] = &$items[$item[$fieldPri]];
            } else {
                $tree[] = &$items[$item['id']];
            }
        }
        return $tree;
    }

}
