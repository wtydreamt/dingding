<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\Session;
use think\view;
class Buckle extends Common
{
  private $corpid = "";
  private $userid = "";

  public function __construct(){
       parent::__construct();
       $this->corpid = session::get("corpid");
       $this->userid = session::get($this->corpid."userid");
  }

	/**
	 * [index 入口方法]
	 * @param  [string] $corpid [企业corpid]
	 */
    public function index()
    {		
          //获取jsapi鉴权信息
    	    $config = DingCache::IsvConfig($this->corpid);
          //获取用户信息
          $user = model("User")->GetUser();
          $userinfo = json_encode($user);

          $Events_list=model("Events")->GetEvents($type = "0",$parent = "0");

          $view = new View();
          $view->title = "奖扣申请";
          $view->config = $config;
          $view->userinfo = $userinfo;
          $view->Events_list = $Events_list;
          return $view->fetch();  
    }

    public function get_events($id = "",$name = ""){

           $Events_list=model("Events")->GetEvents($type = "0" ,$id ,$name);

           echo ReturnJosn("ok","0",$Events_list);
    }

    public function get_events_info($id = ""){

           $Events_info = model("Events")->get_event_info($id);
           echo ReturnJosn("ok","0",$Events_info);

    }

}
