<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\Session;
use think\view;
class Fabulous extends Common
{
	private $Ding = "";

	public function __construct(){
		   parent::__construct();
	}

	/**
	 * [index 入口方法]
	 * @param  [string] $corpid [企业corpid]
	 */
    public function index()
    {		
    	    $corpid = session::get("corpid");
    	    $config = DingCache::IsvConfig($corpid);
          $list   = model("Events")->GetEvents();
          $view = new View();
          $view->title = "赞赏";
          $view->config = $config;
          $view->list   = $list;
          return $view->fetch();  
    }



}
