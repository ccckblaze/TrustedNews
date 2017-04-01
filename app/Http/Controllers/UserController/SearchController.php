<?php

namespace App\Http\Controllers\UserController;

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
        //$cws->set_multi(3);
        //$cws->set_ignore(true);
        //$cws->set_debug(true);
        //$cws->set_duality(true);
    }

    public function search()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['last_search'])) {
            $timePast = time() - $_SESSION['last_search'];
            if($timePast < 10){
                return "Slow down~ Buddy!";
            }
        }
        $_SESSION['last_search'] = time();

        $news = [];
        $searchTitle = "";
        $searchText = "";
        $query = DB::table("news");
        if (isset($_GET["url"])) {
            #ini_set('user_agent', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:38.0) Gecko/20100101 Firefox/38.0');
            $result = file_get_contents($_GET["url"]);
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
            if (isset($_GET["title"])) {
                //$news = DB::select(\'select * from news where title like ?\', ["%".$_GET["title"]."%"]);
                $this->cws->send_text($_GET["title"]);
                while ($tmp = $this->cws->get_result()) {
                    foreach ($tmp as $w) {
                        $searchTitle .= $w['word'];
                        $searchTitle .= "%";
                    }
                }
                if (strlen($searchTitle)) {
                    $query = $query->where("title", "like", "%" . $searchTitle);
                }
            }
            if (isset($_GET["text"])) {
                $this->cws->send_text($_GET["text"]);
                while ($tmp = $this->cws->get_result()) {
                    foreach ($tmp as $w) {
                        $searchText .= $w['word'];
                        $searchText .= "%";
                    }
                }
                if (strlen($searchText)) {
                    $query = $query->where("content", "like", "%" . $searchText);
                }
            }
        }
        if (count($query->getBindings())) {
            $news = $query->simplePaginate(10, ['*'], 'page');//->get();
            $news->appends(Request::all()); // append query string
            // format content
            foreach ($news as $item) {
                $item->content = NetworkUtils::TrimHtml($item->content);
            }
        }
        return $news;
    }
}
