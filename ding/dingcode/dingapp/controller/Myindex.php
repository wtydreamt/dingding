<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;
use app\dingapp\model\Office;
use think\dingapi\DingCache;
use think\session;

class Myindex extends Common
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
        
    	$user = new User();
    	$userinfo=$user->GetUser();
    	return $this->fetch("index",["title"=>"我的","user"=>$userinfo]);

    }

    //组织架构
    public function framework(){

    	$user = new User();

    	$framework= $user->getframework();
        $corpid = session::get("corpid");
        $config = DingCache::IsvConfig($corpid);
    	return $this->fetch("framework",['title'=>"组织架构","config"=>$config,"user"=>$framework['user'],"office"=>$framework['office']]);

    }

}
