<?php
namespace app\dingapp\model;
use think\Model;

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

}