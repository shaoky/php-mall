<?php

namespace Common\ORG\Util;

use Think\Log;


/**
 * Thinkphp 日志封装
 * @package extend\util
 */
class Logger
{

    private static function logger($message, $level)
    {
        Log::write($message, $level);
    }

    /**
     * 错误日志
     * @param string $message
     */
    static public function error($message)
    {
        self::logger($message, Log::ERR);
    }

    /**
     * 信息日志
     * @param string $message
     */
    static public function info($message)
    {
        self::logger($message, Log::INFO);
    }

    /**
     * 调试日志
     * @param string $message
     */
    static public function debug($message)
    {
        self::logger($message, Log::DEBUG);
    }

    /**
     * 警告日志
     * @param string $message
     */
    static public function warn($message)
    {
        self::logger($message, Log::WARN);
    }

}