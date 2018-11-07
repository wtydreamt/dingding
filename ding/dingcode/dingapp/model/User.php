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
	  	     $cust_id = session::get("corpid");

	  	     if($data['errmsg']  == "ok"){

	  	     	$res = Db::table("sys_user")->where("cust_id", $cust_id)->where("dd_id", $data['userid'])->field('id')->find();
	  	     	
	  	     	if($res){

	  	     		session::set($cust_id."userid",$data['userid']);
	  	     		return ReturnJosn("ok","D_200","已登录状态");

	  	     	}else{

	  	     		$user = array();
	  	     		$user['name']    = $data['name'];
	  	     		$user['dd_id']   = $data['userid'];
	  	     		$user['cust_id'] = $cust_id;
	  	     		$user['dd_avatar']   = $data['avatar'];
	  	     		$user['office_id']   = $data['department']['0'];
	  	     		$user['is_dd_admin'] = ($data['isAdmin'])?"1":0;
	  	     		$user['create_date'] = date("Y-m-d H:i:s");
	  	     		$user['dingding_office_id'] = implode($data['department'],",");
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
	        $user  = Db::table('sys_user')
	        ->alias('u')
	        ->where("dd_id",$userid)
	        ->where("u.cust_id",$corpid)
	        ->join('dingding_office o ','o.dingding_id = u.office_id ')
	        ->where("o.cust_id",$corpid)
	        ->field("u.name,u.dd_avatar,o.name as o_name,u.dd_avatar")->find();
	        return $user;	  	     
	  }

	  /**
	   * 获取组织架构
	   */
	  
	  public function getframework(){
	  	     $corpid=session::get("corpid");
	  	     $user=Db::table("sys_user")->where("cust_id",$corpid)->field("name,office_id")->select();
	  	     $usertemp = [];
	  	     foreach($user as $key=>$val){
	  	            $usertemp[$val['office_id']][$key] = $val;
	  	     }
	  	     $office = Db::table("dingding_office")->where("cust_id",$corpid)->field("name,parent_id,dingding_id")->select();
	  	     return array("user"=>$usertemp,"office"=>$office);

	  }
}