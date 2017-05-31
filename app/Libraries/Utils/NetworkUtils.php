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

    static public function CheckSessionPeriod($limit, $key = ""){
        // Check Session Period
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['last'.$key])) {
            $timePast = time() - $_SESSION['last'.$key];
            if($timePast < $limit){
                return false;
            }
        }
        $_SESSION['last'.$key] = time();

        return true;
    }
}