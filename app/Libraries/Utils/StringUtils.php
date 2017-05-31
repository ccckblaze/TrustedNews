<?php
/**
 * Created by PhpStorm.
 * User: 4399-1353
 * Date: 2017/5/5
 * Time: 10:38
 */

namespace App\Libraries\Utils;


class StringUtils
{
    static public function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}