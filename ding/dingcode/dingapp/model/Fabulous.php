<?php
namespace app\dingapp\model;
use think\Model;
use think\Session;
use think\Db;
use think\dingapi\SendMessage;
class Fabulous extends Model
{	  
	  protected $table = 'approval';

	  public function approval($approval_S,$event_arr,$apply_user_id,$code){  	
	  	     //创建点赞事件申请
	  		 $approval=model("Fabulous");
             $approval->startTrans();
             $name = date("Y-m-d").".txt";
	  		 $res = $approval->allowField(true)->save($approval_S);
	  		 if(!$res){
	  		 	$approval->rollback();
	  		 	error_log ( json_encode(array("创建点赞事件"=>$approval_S),JSON_UNESCAPED_UNICODE),3,"log/"."err".$name );
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
	  		 	error_log ( json_encode(array("创建点赞事件记录"=>$event_arr),JSON_UNESCAPED_UNICODE),3,"log/"."err".$name );
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
	  		 	error_log ( json_encode(array("福分不足"=>$balance,"code"=>$code,"dd_id"=>$approval_S['create_user_id']),JSON_UNESCAPED_UNICODE),3,"log/"."err".$name );
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
			    error_log ( json_encode(array("奖扣分数扣减异常"),JSON_UNESCAPED_UNICODE),3,"log/"."err".$name );	  		 	
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
	  

	  // 获取点赞列表
	  
	  public function GetBuckle(){

	  	     $corpid = session::get("corpid");
	  		 return  $likes  = $this->alias('a')
	  		 ->join("approval_event e" , " a.id = e.approval_id")
	  		 ->join("approval_derive d", "e.id = d.a_id")
	  		 ->where("a.corp_id",$corpid)->where("e.is_likes",1)->where("e.like_id",0)
	  		 ->field("d.id,e.id as like_id,a.create_user_id as create_user, d.user_id as s_user, a.create_date as a_date,e.label,e.event_desc")
	  		 ->group('d.a_id')->select();

	  }

	  // 获取奖扣列表
	  public function GetEvent(){

	  	     $corpid = session::get("corpid");
	  		 return  $likes  = $this->alias('a')
	  		 ->join("approval_event e" , " a.id = e.approval_id")
	  		 ->join("approval_derive d", "e.id = d.a_id")
	  		 ->where("a.corp_id",$corpid)->where("e.is_likes",0)->where("is_month",0)
	  		 ->field("a.create_user_id, a.create_date as a_date,e.event_name, count(d.a_id) as num,code_b,code_c")
	  		 ->group('d.a_id')->select();

	  }
	  /**
	   * [Myreview 我的审核]
	   * @param integer $First_trial [初审或终审 0 初审 1终审]
	   * @param [type]  $date        [申请创建时间]
	   * @param integer $status      [审核状态 0 初审中 1 终审中 2通过 3驳回]
	   */
	  public function Myreview($First_trial = 0, $date = 0, $status = 10){
	  	     $corpid = session::get("corpid");
	  	     $userid = session::get($corpid."userid");
	  	     $model_a  = model("Approvale")->alias("e")->join(" approval a ", " e.approval_id = a.id ")
	  	               ->join(" sys_user u ", " u.dd_id = a.create_user_id ")
	  	               ->where("e.is_likes",0)->where("is_month",0)->where("a.corp_id",$corpid);

			  	       if($First_trial == 0){
			  	       	 $query_where = $model_a->where("first_user_id",$userid);
			  	       }else if($First_trial == 1){
			  	       	 $query_where = $model_a->where("end_user_id",$userid);
			  	       }

			  	       if($date){
			  	       	 $query_where = $query_where->where('DATE_FORMAT(a.create_date,"%Y-%m-%d") ='."'$date'");
			  	       }

			  	       if($status != 10){
			  	       	 $query_where = $query_where->where("status",$status);
			  	       }
			         return $data_list = $query_where->distinct(true)->field("a.create_date,e.id,e.event_name,a.status,u.name,u.dd_avatar,e.date,e.event_desc")->order('a.create_date desc')->select();


	  }

	  //获取需要审批的数据
	  public function Approval_detail($id, $First_trial){

	  		 $corpid = session::get("corpid");
	  		 $userid = session::get($corpid."userid");
	  	     //获取审批详情
	  		 $obj = model("Approvale")->alias("e")
	  		 ->join(" approval a "," e.approval_id = a.id ")
	  		 ->where("a.corp_id",$corpid)->where("e.id",$id);

	  		 if($First_trial == "MY_End"){
	  		 	$obj = $obj->where("end_user_id",$userid);
	  		 }else{
	  		 	$obj = $obj->where("first_user_id",$userid);
	  		 }
	  		 $info = $obj->field("a.id,a.status,a.create_user_id,a.end_date,a.create_date,e.event_name,a.first_remark,a.end_remark,e.event_desc,e.date,first_user_id,end_user_id")->find();
	  		 $user_where = [$info['create_user_id'],$info['first_user_id'],$info['end_user_id']];
	  		 //获取奖扣人名称
	  		 $user = model("user")->where("dd_id","in",$user_where)->where("cust_id",$corpid)->field("dd_id, name, dd_avatar")->select();
			 $temp_user = "";

			 foreach($user as $key=>$val){
               
               $temp_user[$val['dd_id']]['name'] = $val['name'];
               $temp_user[$val['dd_id']]['dd_avatar'] = $val['dd_avatar'];

			 }
	  		 //获取奖扣对象列表
	  		 $userlist  = model("Approvald")->alias("d")->join(" sys_user u "," d.user_id = u.dd_id ")
	  		 		     ->where("d.a_id",$id)->where("d.cust_id",$corpid)->where("u.cust_id",$corpid)
	  		             ->field("d.code_b, d.code_c, u.name, u.dd_avatar")->select();
	  		 return array("info"=>$info,"user"=>$temp_user,"userlist"=>$userlist);
	  }

	  //审核是否通过
	  public function is_adopt($id, $desc, $status, $type){
	  		 $corpid = session::get("corpid");
	  		 $userid = session::get($corpid."userid"); 

             $info_obj = model("Fabulous")->alias("a")->join("approval_event e","a.id=e.approval_id")
             ->where("a.corp_id",$corpid)->where("a.id",$id);

             $save_data = [];
             //初审 或 终审通过
             if($type == "end"){
             	$save_data['end_date'] = date("Y-m-d H:i:s");
             	$save_data['status']   = "2";
             	$save_data['end_remark']   = $desc;
             	$info = $info_obj->where("end_user_id",$userid)->field("create_user_id,end_user_id,first_user_id,event_name,a.id as a_id, e.id as e_id")->find();
             }else if($type == "first"){
             	$save_data['first_date'] = date("Y-m-d H:i:s");
             	$save_data['status']     = "1";
             	$save_data['first_remark']   = $desc;
             	$info = $info_obj->where("first_user_id",$userid)->field("create_user_id,event_name,end_user_id,first_user_id,a.id as a_id, e.id as e_id")->find();
             }
             //审核驳回
             if($status == "reject"){
             	$save_data['status']     = "3";
             } 
             $res = $this->adopt_or_reject($id,$save_data,$info);

             if($res == 1){

             	$res_send = json_encode(array("errcode"=>"D0")); //没有消息通知

             	//初审或终审通过时 发送工作通知
		        if($type == "first" && $status == "adopt"){
		 	          $res_send = $this->sendmsg($corpid, $info, $info['end_user_id'], $type, $status);
		        }else if($type == "end" && $status == "adopt"){
		        	  $res_send = $this->sendmsg($corpid, $info, "", $type, $status);
		        }
		        $res_send = json_decode($res_send,true);
		        echo ReturnJosn("ok","0",$res_send);die;
             }else if($res == -1){
             	echo ReturnJosn("ok","-1","");die;
             }



	  }

	  public function sendmsg($corpid,$info,$userobj,$type="",$status=""){
             	 $event_data = "";
	             $userid = model("Approvald")->where("a_id",$info['e_id'])->where("cust_id",$corpid)->field("user_id,code_b,code_c")->select();

	             if($userid['0']['code_b']){
	             	$event_data = ($userid['0']['code_b'] > 0) ? "D分：+".$userid['0']['code_b'] : "D分：".$userid['0']['code_b'];
	             }

	             if($userid['0']['code_c']){
	             	$event_data = ($userid['0']['code_c'] > 0) ? "C分：+".$userid['0']['code_c'] : "C分：".$userid['0']['code_c'];
	             }

	             $userid     = collection($userid)->toArray();
	             $userlist   = array_column($userid,"user_id"); //奖扣人列表	 

	             //终审通过时更改月度记录表
	             if($type == "end" && $status == "adopt"){

		  		 		foreach($userid as $key=>$val){
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
					  		  	  		    
			  		 	        model("Approvalcount")->CheckCount($val['user_id'],$corpid,$data);
		  		 	    }

	             	$userobj = implode(",", $userlist);

	             }

	             $user_name  = model("user")->where("cust_id",$corpid)->where("dd_id","in",$userlist)->field("name")->select(); //奖扣人姓名

	             $user_name  = collection($user_name)->toArray();
	             $userstring = array_column($user_name,"name"); 
	             $userstring = implode(",", $userstring);
	             
	             $create_name=model("User")->getUserName($info['create_user_id']);
	             $first_name =model("User")->getUserName($info['first_user_id']);
	             $end_name=model("User")->getUserName($info['end_user_id']);
				 $json_data = SendMessage::MessageData($corpid,$create_name['name']
				 ,$info['event_name']
				 ,$userstring
				 ,$first_name['name']
				 ,$end_name['name']
				 ,$userobj
				 ,$event_data."测试通知","初审通过");
				 return $res = SendMessage::SendWorkmsg($corpid,$json_data);
	  }

	  public function adopt_or_reject($id,$save_data,$info){

             $approval = model("Fabulous");
             $approval->startTrans();
             $res_a = $approval->save($save_data,['id'=>$id]);
             $approvald = model("Approvald");
             $approvald->startTrans();
             $res_d = $approvald->allowField(true)->save($save_data,['a_id'=>$info['e_id']]);

             if(!$res_d || !$res_a){
	  		 	$approval->rollback();
	  		 	$approvald->rollback();
	  		 	return "-1";exit();           	    
             }  
	  		 $approval->commit();
	  		 $approvald->commit();  
	  		 return "1";exit(); 	  	
	  }
}