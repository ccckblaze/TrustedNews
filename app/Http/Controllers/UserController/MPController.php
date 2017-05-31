<?php

namespace App\Http\Controllers\UserController;

use DB;
use Request;
use Validator;
use App;
use App\User;
use App\Http\Controllers\Controller;
use App\Libraries\Utils\NetworkUtils;
use App\Libraries\Utils\StringUtils;
use WXBizMsgCrypt;

require_once(dirname(__FILE__) . '/../../../Libraries/wxBizMsgCrypt/wxBizMsgCrypt.php');

class MPController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Search Controller
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Create a new mp controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //return "test";
    }

    public function show()
    {
        $resultStr = "";

        // Authorize
        if(isset($_GET["echostr"])){
            return $_GET["echostr"];
        }

        // Handle POST
        if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
            $postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //获取POST数据

            // Encrypted Information
            $encodingAesKey = env('WX_AES_KEY', "");
            $token = env('WX_TOKEN', "");
            $appId = env('WX_APP_ID', "");

            $pc = NULL;

            // Parse POST DATA
            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
            // Check If Encrypted
            if(isset($postObj->Encrypt)){
                $pc = new WXBizMsgCrypt($token, $encodingAesKey, $appId);
                $errCode = $pc->decryptMsg($_GET["msg_signature"], $_GET["timestamp"], $_GET["nonce"], $postStr, $msg);
                if ($errCode == 0) {
                    $postObj = simplexml_load_string($msg,'SimpleXMLElement',LIBXML_NOCDATA);
                } else {
                    print($errCode . "\n");
                    return;
                }
            }

            // Basic Information
            $fromUsername = $postObj->FromUserName; // sender
            $toUsername = $postObj->ToUserName; // receiver
            $msgType = $postObj->MsgType; // type
            $content = trim($postObj->Content); // content
            $timeStamp = time();
            $nonce = StringUtils::generateRandomString();

            $contentStr = "";
            $parseResult = function($result){
                $parsedResult = "";
                if(is_string($result)){
                    $parsedResult .= $result;
                }
                else if(count($result)){
                    if(count($result) > 1){
                        $parsedResult .= "找到超过".count($result)."条相关记录。\n";
                    }
                    $parsedResult .= "该新闻可能为真！\n相关性最高的权威新闻是：\n".$result[0]->refer."\n发布时间为：\n".$result[0]->datetime."\n（搜索结果仅供参考）";
                    //$news = array();
                    //foreach ($result as $item) {
                    //array_push($news, $item->content);
                    //$contentStr .= $item->content;
                    //}
                }
                return $parsedResult;
            };

            switch ($msgType){
                case "text":
                    if(NetworkUtils::CheckSessionPeriod(10, "wp")){
                        // Match Title First
                        $result = App::call('App\Http\Controllers\UserController\SearchController@search', ["params" => ["title" => $content, "format" => false, "paging" => false, "checkSession" => false]]);
                        $contentStr .= $parseResult($result);

                        // Match Text Second
                        if(empty($contentStr)){
                            $result = App::call('App\Http\Controllers\UserController\SearchController@search', ["params" => ["text" => $content, "format" => false, "paging" => false, "checkSession" => false]]);
                            $contentStr .= $parseResult($result);
                        }
                    }
                    else{
                        return "搜索过于频繁，请稍后再试！";
                    }
                    break;
                case "event":
                    switch ($postObj->Event){
                        case "subscribe":
                            $contentStr .= "可信闻——您身边的辟谣专家！\n输入新闻的关键字，即刻帮您判断新闻真伪！";
                            break;
                        default:
                            $contentStr .= "当前消息类型暂时不被支持，我们将在后续推出，敬请期待！";
                            break;
                    }
                    break;
                default:
                    $contentStr .= "当前消息类型暂时不被支持，我们将在后续推出，敬请期待！";
                    break;
            }

            // Check Emtpty
            if(empty($contentStr)){
                $contentStr .= "搜索失败！可能原因：\n1、该关键词相关新闻为谣言；\n2、输入的关键词不够准确\n3、该新闻相关资讯不足\n很抱歉%>_<%";
            }

            $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>";

            // Format To XML
            $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $timeStamp, "text", $contentStr);

            // POST Encrypted Data
            if($pc){
                $encryptMsg = '';
                $errCode = $pc->encryptMsg($resultStr, $timeStamp, $nonce, $encryptMsg);
                if ($errCode == 0) {
                    $resultStr = $encryptMsg;
                } else {
                    print($errCode . "\n");
                    return;
                }
            }
            return $resultStr;
        }
        else{
            return "no post data";
        }
    }
}
