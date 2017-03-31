<?php

namespace App\Http\Controllers\UserController;

use DB;
use Request;
use App\User;
use App\Http\Controllers\Controller;
use Validator;
use PSCWS4;

require_once (dirname(__FILE__) . '/../../../Libraries/pscws4/pscws4.class.php');

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
        $this->cws->set_dict(dirname(__FILE__).'/../../../Libraries/pscws4/etc/dict.utf8.xdb');
        $this->cws->set_rule(dirname(__FILE__).'/../../../Libraries/pscws4/etc/rules.utf8.ini');
        //$cws->set_multi(3);
        //$cws->set_ignore(true);
        //$cws->set_debug(true);
        //$cws->set_duality(true);
    }

    public function search()
    {
        $news = [];
        $searchTitle = "";
        $searchText = "";
        $query = DB::table("news");
        if(isset($_GET["title"])){
            //$news = DB::select('select * from news where title like ?', ["%".$_GET["title"]."%"]);
            $this->cws->send_text($_GET["title"]);
            while ($tmp = $this->cws->get_result())
            {
                foreach ($tmp as $w)
                {
                    $searchTitle .= $w['word'];
                    $searchTitle .= "%";
                }
            }
            if(strlen($searchTitle)){
                $query = $query->where("title", "like", "%".$searchTitle);
            }
        }
        if(isset($_GET["text"])){
            $this->cws->send_text($_GET["text"]);
            while ($tmp = $this->cws->get_result())
            {
                foreach ($tmp as $w)
                {
                    $searchText .= $w['word'];
                    $searchText .= "%";
                }
            }
            if(strlen($searchText)){
                $query = $query->where("content", "like", "%".$searchText);
            }
        }
        if(count($query->getBindings())){
            $news = $query->simplePaginate(10, ['*'], 'page');//->get();
            $news->appends(Request::all()); // append query string
            // format content
            foreach ($news as $item){
                $item->content = strip_tags($item->content);
                $item->content = preg_replace('/\s+/', '', $item->content);
                $item->content = str_replace("&nbsp;", "", $item->content);
            }
        }
        return $news;
        #}
        #else{
        #    return "invalid";
        #}
    }
}
