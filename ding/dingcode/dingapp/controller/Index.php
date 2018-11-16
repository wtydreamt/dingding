<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use app\dingapp\model\User;
use app\dingapp\model\Office;
use think\Session;
use think\Db;


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
		    $cust_id = session::get("corpid");
			$userid  = session::get($cust_id."userid");
			if($userid){
			echo ReturnJosn("ok","D_200","已登录状态");die;	
			}
			$user=DingCache::get_user($corpid,$code);
			$user=json_decode($user,true);
			$userinfo=DingCache::get_user_info($corpid,$user['userid']);
			$user = new User();
			echo  $res=$user->UserRegister($userinfo);		

	}

	//首页--点赞数据
	public function home(){
		   $Fabulous = model("Fabulous")->GetBuckle();
		   $Fabulous = collection($Fabulous)->toArray();
		   $c_user   = array_column($Fabulous,'create_user');  //获取当前记录赞赏人
		   $s_user   = array_column($Fabulous,'s_user');       //获取当前受赞赏人

		   $c_user_list= model("user")->GetAll($c_user);
		   $s_user_list= model("user")->GetAll($s_user);
		   $user_list  = array_merge($c_user_list,$s_user_list);

		   $event_list=$this->getEvent();


		   return $this->fetch("home",["title"=>"悦积分","Fabulous"=>$Fabulous,"user_list"=>$user_list,"event"=>$event_list]);

	}

	//首页奖扣数据
	public function getEvent(){

		   $arr=model("Fabulous")->GetEvent();
		   $arr=collection($arr)->toArray();
		   $event_user=array_column($arr,'create_user_id');
		   $user_list= model("user")->GetAll($event_user);
		   return array("event"=>$arr,"user"=>$user_list);

	}
}
