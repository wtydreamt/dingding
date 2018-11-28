<?php
namespace app\dingapp\model;
use think\Model;
use think\Session;
class Approvalcount extends Model
{	  
	  //分数月度统计记录表
	  protected $table = 'approval_count';
	  public function Approvalcount($userid,$corpid){
	  		 //获取当前年份  获取当前月份
	  		 $year = date("Y");
	  		 $month= date("m");
	  		 return $this->where("year",$year)->where("month",$month)->where("user_id",$userid)->where("cust_id",$corpid);

	  }

	  //创建事件月度记录
	  public function CountInsert($userid,$corpid,$data){
             $arr = [];
             $arr['user_id'] = $userid;
             $arr['cust_id'] = $corpid;
             $arr['year']    = date("Y");
             $arr['month']   = date("m");
             foreach($data as $key=>$val){
                 $arr[$key]=$val;	
             }
             $this->save($arr);
	  }

	  //记录事件月得分情况 
	  public function CheckCount($userid,$corpid,$data){
			 //检测当月记录
			 $info = $this->Approvalcount($userid,$corpid)->field("user_id")->find();
			 if($info){
			 //如果当月记录存在进行更改
			 $res  = $this->CountSetInc($userid,$corpid,$data);
			 }else{
			 //如果当月记录不存在进行创建
			 $res  = $this->CountInsert($userid,$corpid,$data);
			 }	  
	  }
	  //修改月得分
	  public function CountSetInc($userid,$corpid,$data){
	  	     foreach($data as $key=>$val){
	  	     $this->Approvalcount($userid,$corpid)->setInc($key,$val);	
	  	     }
	  }

	  //获取个人当月累计积分
	  public function PersonalMonth(){
	  		 $corpid = session::get("corpid");
	  		 $userid = session::get($corpid."userid");
	  		 //获取当前年份  获取当前月份
	  		 $year = date("Y");
	  		 $month= date("m");	  		 
	  	     return $this->where("year",$year)->where("month",$month)->where("user_id",$userid)->where("cust_id",$corpid)
	  	     ->field("(code_be + buckle_be) as number")->find();

	  }

	  //获取当日积分
	  public function GetToday(){
	  	     $corpid = session::get("corpid");
	  	     $userid = session::get($corpid."userid");
	  	     $date = date("Y-m-d");
	  		 $res  = model("Approvale")->alias("e")->join("approval_derive d","e.id = d.a_id")->where("e.corp_id",$corpid)
	  		 ->where("d.user_id",$userid)->where("d.cust_id",$corpid)
	  		 ->where("e.is_likes",0)->where("e.date",$date)
	  		 ->where("d.status",2)
	  		 ->field("sum(code_b) as day_code")->find();
	  		 return $res;
	  }
}