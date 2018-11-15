<?php
namespace app\dingapp\model;
use think\Model;

class Approvale extends Model
{	  
	  protected $table = 'approval_event';

	  public function BuckleInsert($app_data,$event_data,$userlist,$number){

	  	     $approval = model("fabulous");
	  		 $approval->startTrans();
	  		 $res_app= $approval->allowField(true)->save($app_data);

	  		 if(!$res_app){
		  		$approval->rollback();
		  		return ReturnJosn("NO","-1","事件提交失败");die;
	  		 }

	  		 //创建事件件记录
	  		 $Approvale = model("Approvale");
	  		 $Approvale->startTrans();
	  		 $event_data['approval_id'] = $approval->id;
	  		 $res_event=$Approvale->allowField(true)->save($event_data);

	  		 if(!$res_event){
	  		 	$approval->rollback();
	  		 	$Approvale->rollback();
	  		 	return ReturnJosn("NO","-1","事件提交失败");die;
	  		 }
	  		 
	  		 $Approvald = model("Approvald");
	  		 $Approvald->startTrans();
	  		 $Approvald_arr = [];
	  		 //生成派生表数据	  		 
	  		 $a_id = $Approvale->id;
	         $Approvald_arr = [];
	         foreach($userlist as $key=>$val){
	           $Approvald_arr[$key]['a_id']    = $a_id;
	           $Approvald_arr[$key]['user_id'] = $val['emplId'];
	           $Approvald_arr[$key]['code_c']  = $number['code_c'];
	           $Approvald_arr[$key]['code_b']  = $number['code_b'];
	           $Approvald_arr[$key]['cust_id'] = $app_data['corp_id'];
	           $Approvald_arr[$key]['status']  = 0; //审核状态默认为0;
	         }	
	         //事件记录派生表数据插入
	         $res_appd = $Approvald->saveAll($Approvald_arr);
	         if(empty($res_appd)){
	  		 	$approval->rollback();
	  		 	$Approvale->rollback();
	  		 	$Approvald->rollback();
	  		 	return ReturnJosn("NO","-2","事件提交失败");die;
	         }else{
	  		 	$approval->commit();
	  		 	$Approvale->commit();
	  		 	$Approvald->commit();	
	  		 	
	  		 	foreach($Approvald_arr as $key=>$val){
	  		 		$data = [];
		  		    if($val['code_c'] && $val['code_c'] > 0){
		  		       $data['code_c'] = $val['code_c'];
		  		    }else if($val['code_c'] && $val['code_c'] < 0){
		  		       $data['buckle_c'] = $val['code_c'];
		  		    }
		  		    if($val['code_b'] && $val['code_b'] > 0){
		  		       $data['code_be'] = $val['code_b'];
		  		    }else if($val['code_b'] && $val['code_b'] < 0){
		  		       $data['buckle_be'] = $val['code_b'];
		  		    }	  		    
		  		 	model("Approvalcount")->CheckCount($val['user_id'],$event_data['corp_id'],$data);

	  		 	}
	  		 	
	  		 	return ReturnJosn("OK","200","事件提交成功");die;         	
	         } 		 
	  }

}