<?php
/**
 * Created by PhpStorm.
 * User: 4399-1353
 * Date: 2017/4/1
 * Time: 9:42
 */

namespace App\Libraries\Utils;


class NetworkUtils
{
    static public function TrimHtml($data, $textOnly = false){
        $result = "";
        if($textOnly) {
            if (preg_match_all('%<p[^>]*>((\s*.*?\s*)*)</p>%i', $data, $matches)) {
                foreach ($matches[1] as $match) {
                    $result .= $match;
                }
            }
        } else{
            $result = $data;
        }
        $result = strip_tags($result);
        $result = preg_replace("/\s+/", "", $result);
        $result = str_replace("&nbsp;", "", $result);
        return $result;
    }
}