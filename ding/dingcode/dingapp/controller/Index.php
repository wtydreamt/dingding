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
    public function index($corpid = "")
    {		

    	   if($this->corpid && $this->userid){
    	  	 $corpid = session::get("corpid");
    	   }else{
    	  	 session::set("corpid",$corpid);
    	   }
    	   // model("Office")->Framework();
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
		   $follow   = $this->getfollow($Fabulous);
		   $c_user   = array_column($Fabulous,'create_user');  //获取当前记录赞赏人
		   $s_user   = array_column($Fabulous,'s_user');       //获取当前受赞赏人

		   $c_user_list= model("user")->GetAll($c_user);
		   $s_user_list= model("user")->GetAll($s_user);
		   $user_list  = array_merge($c_user_list,$s_user_list);
		   $event_list=$this->getEvent();

		   return $this->fetch("home",["title"=>"悦积分","Fabulous"=>$Fabulous,"user_list"=>$user_list,"event"=>$event_list,"follow"=>$follow['follow'],"userid"=>$follow['userid']]);

	}

	public function getfollow($Fabulous){
		   //获取跟赞数据
		   $arr = array_column($Fabulous,'like_id');

		   $follow_data= model("Approvale")->where("corp_id",$this->corpid)->where("is_likes",2)->where("like_id","in",$arr)->field("approval_id,event_desc,create_time,like_id")->select();
		   $tempd = [];
		   $u_id  = [];
		   if(!empty($follow_data)){
			   $data = collection($follow_data)->toArray();
			   $approval_id_list = array_column($data,'approval_id');
			   $userlist = model("Fabulous")->alias("a")
			   ->join("sys_user u","a.create_user_id=u.dd_id")
			   ->where("a.corp_id",$this->corpid)->where("u.cust_id",$this->corpid)
			   ->where("a.id","in",$approval_id_list)
			   ->field("u.name,a.id,u.dd_id")->select();
			   $userlist = collection($userlist)->toArray();
			   $u_id = array_column($userlist,'dd_id');
			   $tempu = [];
			   foreach($userlist as $key=>$val){
			   	       $tempu[$val['id']] = $val['name'];
			   }
			   foreach($data as $dkey=>$dval){
			   	      $tempd[$dval['like_id']]['data'][$dkey]['name'] = $tempu[$dval['approval_id']];
			   	      $tempd[$dval['like_id']]['data'][$dkey]['desc'] = $dval['event_desc'];
			   	      $tempd[$dval['like_id']]['data'][$dkey]['date'] = $dval['create_time'];
			   	      $tempd[$dval['like_id']]['count'] = $dkey+1;
			   }
		   }
		   return array("follow"=>$tempd,"userid"=>$u_id);

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
