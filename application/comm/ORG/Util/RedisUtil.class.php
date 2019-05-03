<?php

namespace Common\ORG\Util;


class RedisUtil
{
    private static $handler;


    public static function handler()
    {
        if (!self::$handler) {
            self::$handler = new \Redis();
            self::$handler->connect('127.0.0.1', '6379');
        }
        return self::$handler;
    }

    /**
     * 只能获取string类型
     * @param $key
     * @return bool|string
     */
    public static function get($key)
    {
        $value = self::handler()->get($key);
        $val = @unserialize($value);
        if (is_object($val) || is_array($val)) {
            return $val;
        }
    }

    public static function set($key, $val)
    {
        if (is_object($val) || is_array($val)) {
            $val = serialize($val);
        }
        return self::handler()->set($key, $val);
    }

    public function del($key)
    {
        self::handler()->delete($key);
        return $this->getVal($key);
    }


    public function lIndex($key, $index)
    {
        return self::handler()->lIndex($key, $index);
    }

    public function zCard($key)
    {
        return self::handler()->zCard($key);
    }

    public function sInter($key1, $key2, $keyN = null)
    {
        return self::handler()->sInter($key1, $key2, $keyN);
    }

}