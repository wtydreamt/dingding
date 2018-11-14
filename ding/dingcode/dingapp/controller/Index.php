<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use app\dingapp\model\User;
use app\dingapp\model\Office;
use think\Session;


class Index extends Common
{
	private $Ding = "";

	public function __construct(){
		   parent::__construct();
	}
	/**
	 * [index 入口方法]
	 * @param  [string] $corpid [企业corpid]
	 */
    public function index($corpid = "")
    {		

    	   if(session::get("corpid") && session::get("corpid") == $corpid){
    	  	 $corpid = session::get("corpid");
    	   }else{
    	  	 session::set("corpid",$corpid);
    	   }
    	   
    	   $config = DingCache::IsvConfig($corpid); 
    	   return $this->fetch("index",['corpid'=>$corpid,"config"=>$config,"title"=>"悦积分"]);

    }
	

	//获取用户注册信息
	public function getuser($corpid="" ,$code="" ){


			$user=DingCache::get_user($corpid,$code);
			$user=json_decode($user,true);
			$userinfo=DingCache::get_user_info($corpid,$user['userid']);
			$user = new User();
			echo  $res=$user->UserRegister($userinfo);		

	}

}
