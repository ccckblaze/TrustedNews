<?php

namespace App\Http\Controllers\UserController;

use DB;
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
        if(isset($_GET["text"])){
            //$news = DB::select('select * from news where title like ?', ["%".$_GET["text"]."%"]);
            $search = "";
            $this->cws->send_text($_GET["text"]);
            while ($tmp = $this->cws->get_result())
            {
                foreach ($tmp as $w)
                {
                    $search .= $w['word'];
                    $search .= "%";
                }
            }

            $news = DB::table("news")->where("title", "like", "%".$search)->get();
            return $news;
        }
        else{
            return "invalid";
        }
    }
}
