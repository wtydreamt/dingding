<?php
namespace app\dingapp\model;
use think\Model;

class Fabulous extends Model
{	  
	  protected $table = 'approval';

	  public function approval($approval_S,$event_arr,$apply_user_id,$code){  	
	  	     //创建点赞事件申请
	  		 $approval=model("Fabulous");
             $approval->startTrans();

	  		 $res = $approval->allowField(true)->save($approval_S);
	  		 if(!$res){
	  		 	$approval->rollback();
	  		 	return ReturnJosn("NO","-1","事件提交失败");die;
	  		 }
	  		 //创建点赞事件记录
	  		 $Approvale = model("Approvale");
	  		 $Approvale->startTrans();
	  		 $event_arr['approval_id'] = $approval->id;
	  		 $res_event=$Approvale->allowField(true)->save($event_arr);
	  		 if(!$res_event){
	  		 	$approval->rollback();
	  		 	$Approvale->rollback();
	  		 	return ReturnJosn("NO","-1","事件提交失败");die;
	  		 }
	  		 //事件记录派生表数据插入
	  		 $Approvald = model("Approvald");
	  		 $Approvald->startTrans();
	  		 $Approvald_arr = [];
	  		 //生成派生表数据
	  		 $Approvald_arr['a_id']    = $Approvale->id;
	  		 $Approvald_arr['code_a']  = $code;
	  		 $Approvald_arr['user_id'] = $apply_user_id;
	  		 $Approvald_arr['cust_id'] = $approval_S['corp_id'];
	  		 $Approvald_arr['status']  = 2;
	  		 $res_d = $Approvald->allowField(true)->save($Approvald_arr);

	  		 $balance = model("user")->where("dd_id",$approval_S['create_user_id'])->where("cust_id",$approval_S['corp_id'])->field("balance")->find();

	  		 if(!$res_d || $balance['balance']*100 < $code){
	  		 	$approval->rollback();
	  		 	$Approvale->rollback();
	  		 	$Approvald->rollback();
	  		 	return ReturnJosn("NO","-1","事件提交失败");die;	  		 	
	  		 }
	  		 $user = model("User");
	  		 $user->startTrans();
	  		 //扣减赞赏人福分
	  		 $res_Dec=$user->where("dd_id",$approval_S['create_user_id'])->where("cust_id",$approval['corp_id'])->setDec("balance",$code);
	  		 // 增加被赞赏人福分
	  		 $res_Inc=$user->where("dd_id",$apply_user_id)->where("cust_id",$approval_S['corp_id'])->setInc("balance",$code);

	  		 if(!$res_Dec || !$res_Inc){
	  		 	$approval->rollback();
	  		 	$Approvale->rollback();
	  		 	$Approvald->rollback();
	  		 	$user->rollback();
	  		 	return ReturnJosn("NO","-1","奖扣分数扣减异常");die;
	  		 }else{
	  		 	$approval->commit();
	  		 	$Approvale->commit();
	  		 	$Approvald->commit();
	  		 	$user->commit();
	  		 	//记录月份分数扣减情况
	  		    model("Approvalcount")->CheckCount($approval_S['create_user_id'],$approval['corp_id'],['buckle_a'=>"-".$code]);
	  		    model("Approvalcount")->CheckCount($apply_user_id,$approval['corp_id'],['code_a'=>$code]);		  		 	
	  		 	return ReturnJosn("ok","200","数据写入正常"); 		 	
	  		 }	  		 
	  }
	  

	  //获取奖扣列表
	  
	  // public function GetBuckle(){

	  // 		 $this->alias('a')
	  // 		 ->join("user u" , " a.corp_id = u.cust_id")
	  // 		 ->join("")

	  // }
}