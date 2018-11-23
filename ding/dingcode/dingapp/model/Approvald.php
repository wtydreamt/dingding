<?php
namespace app\dingapp\model;
use think\Model;
use think\session;
class Approvald extends Model
{	  
	  protected $table = 'approval_derive';

	  //获取奖扣列表
	  public function award_list($page,$time,$status){

	  		 $corpid = session::get("corpid");
	  		 $userid = session::get($corpid."userid");

	  		 $query  = model("Approvale")->alias("e")
	  		 ->join("approval_derive d"," e.id = d.a_id ")
	  		 ->join("sys_user u"," u.dd_id = d.user_id ")
	  		 ->where("e.corp_id",$corpid)
	  		 ->where("d.user_id",$userid)
	  		 ->where("u.cust_id",$corpid)
	  		 ->where("d.cust_id",$corpid)
	  		 ->where("e.is_likes",0);

	  		 if($time){
	  		 $query = $query->where("e.date",$time);
	  		 }
	  		 if($status != "10"){
	  		 $query = $query->where("d.status",$status);
	  		 }	  		 
	  		 return $list  = $query->limit($page,3)
	  		 ->field("name,d.status,e.date,e.create_time,event_name,event_desc")->select();
	  		 

	  }
	  
}