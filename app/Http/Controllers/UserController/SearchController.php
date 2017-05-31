<?php

namespace App\Http\Controllers\UserController;

use App\Libraries\Utils\StringUtils;
use DB;
use Request;
use Validator;
use App\User;
use App\Http\Controllers\Controller;
use App\Libraries\Utils\NetworkUtils;
use PSCWS4;

require_once(dirname(__FILE__) . '/../../../Libraries/pscws4/pscws4.class.php');

class SearchController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Search Controller
    |--------------------------------------------------------------------------
    |
    |
    */

    private $cws;

    /**
     * Create a new search controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->cws = new PSCWS4('utf8');
        $this->cws->set_dict(dirname(__FILE__) . '/../../../Libraries/pscws4/etc/dict.utf8.xdb');
        $this->cws->set_rule(dirname(__FILE__) . '/../../../Libraries/pscws4/etc/rules.utf8.ini');
        $this->cws->set_multi(PSCWS4_MULTI_DUALITY | PSCWS4_MULTI_ZMAIN);
        //$this->cws->set_ignore(true);
        //$this->cws->set_debug(true);
        $this->cws->set_duality(true);
    }

    public function customDuality($text)
    {
        $dualities = array();
        $this->cws->send_text($text);

        $buffer = "";
        $checkBuffer = function() use (&$dualities, &$buffer){
            if(mb_strlen($buffer, 'utf8')){
                array_push($dualities, $buffer);
                $buffer = "";
                return true;
            }
            return false;
        };

        while ($tmp = $this->cws->get_result()) {
            foreach ($tmp as $w) {
                // ignore conjunction
                if($w['attr'] != 'c'){
                    // Fix for InnoDB FullText Index
                    if(mb_strlen($w['word'], 'utf8') == 1){
                        // Noun or verb
                        if (0 === strpos($w['attr'], 'n') || 0 === strpos($w['attr'], 'v')){
                            $buffer .= $w['word'];
                        }
                        else{
                            $checkBuffer();
                            //array_push($dualities, $w['word']);
                        }
                    }
                    else{
                        $checkBuffer();
                        array_push($dualities, $w['word']);
                    }
                }
            }
            $checkBuffer();
        }
        return $dualities;
    }

    public function search($params = array())
    {
        $setParam = function($key, $defaultValue = "", $fromGet = false) use (&$params){
            $value = $defaultValue;
            if(isset($params[$key])){
                $value = $params[$key];
            }
            else{
                if($fromGet && isset($_GET[$key])){
                    $value = $_GET[$key];
                }
            }
            return $value;
        };

        // Set Parameters
        $url = $setParam("url", "", true);
        $title = $setParam("title", "", true);
        $text = $setParam("text", "", true);
        $format = $setParam("format", true);
        $paging = $setParam("paging", true);
        $checkSession = $setParam("checkSession", true);

        // Check Session Period
        if($checkSession && !NetworkUtils::CheckSessionPeriod(10)){
            return "搜索过于频繁，请稍后再试！";
        }

        $news = [];
        $query = DB::table("news");
        if (!empty($url)) {
            #ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
            $result = file_get_contents($url);
            $result = NetworkUtils::TrimHtml($result, true);

            $this->cws->send_text($result);

//            $keywords = array();
//            while ($tmp = $this->cws->get_result()) {
//                foreach ($tmp as $w) {
//                    if(!isset($keywords[$w['word']])){
//                        $keywords[$w['word']] = substr_count($result, $w['word']);
//                    }
//                }
//            }
//            array_multisort($keywords);
            foreach ($this->cws->get_tops(20) as $keyword){
                echo $keyword['word'];
            }
        } else {
            if (!empty($title)) {
                foreach ($this->customDuality($title) as $w) {
                    $query = $query->whereRaw("MATCH(title) AGAINST(?)", [$w]);
                }
            }
            if (!empty($text)) {
                foreach ($this->customDuality($text) as $w) {
                    $query = $query->whereRaw("MATCH(content) AGAINST(?)", [$w]);
                }
            }
        }
        if (count($query->getBindings())) {
            if($paging){
                $news = $query->simplePaginate(10, ['*'], 'page');//->get();
                $news->appends(Request::all()); // append query string
            }
            else{
                $news = $query->limit(1);
                $news = $query->get();
            }
            // format content
            foreach ($news as $item) {
                $item->content = NetworkUtils::TrimHtml($item->content);
            }
        }

        if($format){
            $formattedNews = "";
            foreach ($news as $item) {
                $formattedNews .= $item->title;
                $formattedNews .= "<br/>";
                $formattedNews .= $item->content;
                $formattedNews .= "<br/><br/>";
            }
            if(empty($formattedNews)){
                return "无结果。";
            }
            return $formattedNews;
        }
        return $news;
    }
}
