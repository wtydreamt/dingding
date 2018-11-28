<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
use app\dingapp\model\Office;

class User extends Model
{	  
	  protected $table = 'sys_user';

	  public function UserRegister($userinfo){
	  	     $data = json_decode($userinfo,true);

	  	     $cust_id = "dingc74412f7259da97f35c2f4657eb6378f";

	  	     if($data['errmsg']  == "ok"){
	  	     	$res = Db::table("sys_user")->where("cust_id", $cust_id)->where("dd_id", $data['userid'])->field('id,name')->find();
	  	     	if($res){

	  	     		session::set($cust_id."userid",$data['userid']);
	  	     		return ReturnJosn("ok","D_200","已登录状态");

	  	     	}else{

	  	     		$user = array();
	  	     		$user['name']        = $data['name'];
	  	     		$user['position']    = $data['position'];
	  	     		$user['dd_id']       = $data['userid'];
	  	     		$user['cust_id']     = $cust_id;
	  	     		$user['dd_avatar']   = $data['avatar'];
	  	     		$user['office_id']   = $data['department']['0'];
	  	     		$user['is_dd_admin'] = ($data['isAdmin'])?"1":0;
	  	     		$user['create_date'] = date("Y-m-d H:i:s");
	  	     		$user['dingding_office_id'] = implode($data['department'],",");

	  	     		$isLeaderInDepts = trim($data['isLeaderInDepts'],"{");
	  	     		$isLeaderInDepts = trim($isLeaderInDepts,"}");
	  	     		$isLeaderInDepts = explode(",",$isLeaderInDepts);
	  	     		$isLeader = [];
	  	     		$temp = [];
	  	     		foreach($isLeaderInDepts as $key=>$val){
	  	     				$temp = explode(":",$val);
	  	     				if($temp[1] !="false"){
	  	     				   $isLeader[$key] = $temp[0];
	  	     				}
	  	     		}
	  	     		
	  	     		$user['is_dd_leader'] = implode($isLeader,",");
	  	     		$res_u=Db::table("sys_user")->insert($user);
	  	     		if($res_u){
	  	     			session::set($cust_id."userid",$data['userid']);
	  	     			return ReturnJosn("ok","D_200","注册成功");
	  	     		}else{
	  	     			return ReturnJosn("ok","D_400","注册失败");
	  	     		}

	  	     	}

	  	     }else{
	  	     	return ReturnJosn("no","J_401",$data);
	  	     }

	  }

	  /**
	   * [GetUser 获取用户信息]
	   */
	  public function GetUser(){

	  		$corpid=session::get("corpid");
	  		$userid=session::get($corpid."userid");
		    $userinfo  = Db::table('sys_user')
		    ->alias('u')
		    ->where("dd_id",$userid)
		    ->where("u.cust_id",$corpid)
		    ->join('dingding_office o ','o.dingding_id = u.office_id ')
		    ->where("o.cust_id",$corpid)
		    ->field("u.name,u.dd_avatar,o.name as o_name,u.dd_avatar,u.code_b,u.balance,u.dd_id")->find();
	        return $userinfo;
	  }

	  /**
	   * 获取组织架构
	   */
	  
	  public function getframework(){
	  	     $corpid=session::get("corpid");
	  	     $user=Db::table("sys_user")->alias("u")
	  	     ->join("dingding_office o","u.office_id = o.dingding_id")
	  	     ->where("o.cust_id",$corpid)
	  	     ->where("u.cust_id",$corpid)->field("u.name,u.office_id,o.name as oname,position")->select();
	  	     
	  	     $usertemp = [];
	  	     foreach($user as $key=>$val){
	  	            $usertemp[$val['office_id']]["name"] = $val['oname'];
	  	            $usertemp[$val['office_id']]["dingding_id"] = $val['office_id'];
	  	            $usertemp[$val['office_id']]["data"][$key] = $val;
	  	     }
	  	     return $usertemp;

	  }

	  //用户积分扣减行为
	  public function IncDec($type,$field,$userid,$num){

	  		 $corpid=session::get("corpid");
	  		 if($type == "Inc"){
	  		 	$res = $this->where("dd_id=:dd_id AND cust_id=:cust_id")->bind(['dd_id'=>$userid,'cust_id'=>$corpid])->setInc($field,$num);
	  		 }else if($type == "Dec"){
	  		 	$res = $this->where("dd_id=:dd_id AND cust_id=:cust_id")->bind(['dd_id'=>$userid,'cust_id'=>$corpid])->setDec($field,$num);
	  		 }
             return $res;

	  }

	  //获取当前公司所有用户信息
	  public function GetAll($where){

	  		 $corpid=session::get("corpid");

	  		 $userall = $this->where("cust_id=:cust_id")->bind(['cust_id'=>$corpid])->where('dd_id','in',$where)->field("name,dd_id")->select();

	  		 $user_temp = [];

	  		 foreach($userall as $key=>$val){
	  		 	$user_temp[$val['dd_id']] = $val['name'];
	  		 }

	  		 return $user_temp;
	  }

	  //获取用户信息
	  public function getUserName($userid){
	  	     $corpid=session::get("corpid");
	  	     return model("User")->where("cust_id",$corpid)->where("dd_id",$userid)->field("id,name")->find();
	  }

	  //获取用户列表
	  public function userlist($userlist){
	  		 $corpid = session::get("corpid");
	  	     $user = model("User")->where("cust_id",$corpid)->where("dd_id","in",$userlist)->field("name,dd_id")->select();
	  	     $temp = [];
	  	     foreach($user as $key=>$val){
	  	     		 $temp[$val['dd_id']] = $val['name'];
	  	     }
	  	     return $temp;
	  }
}