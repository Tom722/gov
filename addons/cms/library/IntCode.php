<?php

namespace addons\cms\library;

use Hashids\Hashids;

class IntCode
{
    private static $hasids = null;

    /**
     * 初始化
     * @access public
     * @return Hashids
     */
    public static function hashids()
    {
        if (is_null(self::$hasids)) {
            $config = get_addon_config('cms');
            $key = $config['hashids_key'];
            $length = $config['hashids_key_length'] ?? 10;
            $key = $key ? $key : config('token.key');
            self::$hasids = new Hashids($key, $length);
        }
        return self::$hasids;
    }

    /**
     * 加密
     * @param $int
     * @return string
     */
    public static function encode($int)
    {
        return self::hashids()->encode($int);
    }

    /**
     * 解密
     * @param $str
     * @return string
     */
    public static function decode($str)
    {
        $data = self::hashids()->decode($str);
        if (isset($data[0])) {
            return $data[0];
        }
        return null;
    }
}
