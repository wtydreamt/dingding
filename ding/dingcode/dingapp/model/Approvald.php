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
	  		 return $list  = $query->limit($page,10)
	  		 ->field("name,d.status,e.date,e.create_time,event_name,event_desc,d.id")->select();

	  }
	  //奖扣详情
	  public function award_details($id){
	  		 $corpid = session::get("corpid");
	  		 $userid = session::get($corpid."userid");
	  		 $derive = model("Approvald")->alias("d")
	  		 		   ->join("sys_user u","u.dd_id = d.user_id")
	  		 		   ->where("u.cust_id",$corpid)
	  		           ->where("user_id",$userid)
	  		           ->where("d.cust_id",$corpid)
	  		           ->where("d.id",$id)->field("a_id,status,d.code_b,d.code_c,name")->find();

	  		 $event_info = model("Approvale")->alias("e")
	  		           ->join("approval a","a.id = e.approval_id")
	  		           ->join("sys_user u","u.dd_id = a.create_user_id")
	  		           ->where("e.id",$derive['a_id'])
	  		           ->where("e.corp_id",$corpid)
	  		           ->where("a.corp_id",$corpid)
	  		           ->where("u.cust_id",$corpid)
	  		           ->field("u.name,a.create_date,e.date,a.first_date,a.end_date,e.event_name,e.event_desc,a.status,first_remark,end_remark,first_user_id,end_user_id")
	  		           ->find();
	  		  $userid  = [$event_info['first_user_id'],$event_info['end_user_id']];
	  		  $user_name_arr=model("user")->where("dd_id","in",$userid)->where("cust_id",$corpid)->field("name,dd_id")->select();
	  		  $temp = []; 
	  		  foreach($user_name_arr as $key=>$val){
	  		  	      $temp[$val['dd_id']] = $val['name'];
	  		  }
	  		  return   array("apply"=>$event_info,"prize_winner"=>$derive,"user_name"=>$temp);
	  }
	  //福分记录统计
	  public function Blessings($date, $type ="ALL"){
	  	     $corpid = session::get("corpid");
	  	     $userid = session::get($corpid."userid");
	  	     if(!$date){
	  	     	$date = date("Y-m");
	  	     }

	  		 if($type == "Z"){
	  		 	//支出
	  		    $data = $this->derive($corpid,$date)->where("a.create_user_id",$userid)
	  		            ->field("a.create_date,a.create_user_id,d.user_id,d.code_a,e.event_name,'2' as status_code")->select();
	  		            $data = collection($data)->toArray();
	  		 }else if($type == "S"){
	  		 	//收入
	  		    $data = $this->derive($corpid,$date)->where("d.user_id",$userid)
	  		            ->field("a.create_date,a.create_user_id,d.user_id,d.code_a,e.event_name,'3' as status_code")->select();
	  		            $data = collection($data)->toArray();
	  		 }else if($type == "ALL"){
	  		 	//支出
	  		 	$dataz = $this->derive($corpid,$date)->where("a.create_user_id",$userid)
	  		 	         ->field("a.create_date,a.create_user_id,d.user_id,d.code_a,e.event_name,'2' as status_code")->select();
	  		 	$dataz = collection($dataz)->toArray();

	  		 	//收入
	  		 	$datas = $this->derive($corpid,$date)->where("d.user_id",$userid)
	  		 	         ->field("a.create_date,a.create_user_id,d.user_id,d.code_a,e.event_name,'3' as status_code")->select();
	  		 	$datas = collection($datas)->toArray();

	  		 	$data  = array_merge_recursive($datas,$dataz);
	  		 }
	  		 if(!empty($data)){

				 $create_use= array_column($data,'create_user_id');  //获取当前记录赞赏人
				 $user_id   = array_column($data,'user_id');       //获取当前受赞赏人
				 $user_list =array_unique(array_merge_recursive($create_use,$user_id));
				 $user = model("user")->userlist($user_list);

			 }else{
					$data = [];
					$user = [];
			 }
			 if($type == "ALL" || $type == "Z"){
			 	$shop = model("Shoprecord")->consumption($userid,$corpid,$date);
			 	$data = array_merge_recursive($data,$shop);
			 }
	  		 return array($data,$user); 
	  } 

	  public function derive($corpid,$date){
	  		 //获取点赞或跟赞消费收入
	  		 return $query = model("Approvald")->alias("d")
	  		 ->join("approval_event e","d.a_id = e.id")
	  		 ->join("approval a","e.approval_id=a.id")
	  		 ->where("a.corp_id",$corpid)->where("e.corp_id",$corpid)->where("d.cust_id",$corpid)
	  		 ->where('DATE_FORMAT(a.create_date,"%Y-%m") ='."'$date'")->where("d.code_a","<>","0")->where("e.is_likes","<>",0);

	  }

	  //福分收入统计
	  public function income(){
	  	     $corpid = session::get("corpid");
	  	     $userid = session::get($corpid."userid");	  	
			 $user = model("User")->where("dd_id",$userid)->where("cust_id",$corpid)->field("id")->find();
			 //系统发放福分统计
			 $sendf  = model("Blessings")->where("userId",$user['id'])
			 ->where("cust_id",$corpid)->where("trantype","send")
			 ->field("sum(amount) as num")->select();	
			 $sendf = collection($sendf)->toArray();
			 if(empty($sendf)){
			 	$send_number = 0;
			 }else{
			 	$send_number = $sendf['0']['num']*100;
			 }
			 //统计每个月用户 福分收入情况 月度表收入只有 点赞
			 $month_a = model("Approvalcount")->where("user_id",$userid)->where("cust_id",$corpid)->field("sum(code_a) as code_a")->select();
			 $month_a = collection($month_a)->toArray();
			 if(empty($month_a)){
			 	$code_a = 0;
			 }else{
			 	$code_a = $month_a['0']['code_a'];
			 } 
			 return $send_number+$code_a;
	  } 

	  //福分支出统计
	  public function expenditure(){
	  	     $corpid = session::get("corpid");
	  	     $userid = session::get($corpid."userid");	  	
			 $user = model("User")->where("dd_id",$userid)->where("cust_id",$corpid)->field("id")->find();
			 //系统系统回收福分统计
			 $Recoveryf  = model("Blessings")->where("userId",$user['id'])
			 ->where("cust_id",$corpid)->where("trantype","Recovery")
			 ->field("sum(amount) as num")->select();	
			 $Recoveryf = collection($Recoveryf)->toArray();
			 if(empty($sendf)){
			 	$Recovery_number = 0;
			 }else{
			 	$Recovery_number = $sendf['0']['num']*100;
			 }
			 //统计每个月用户 福分支出情况 月度表收入只有 点赞 跟赞 商品兑换
			 $month_a = model("Approvalcount")->where("user_id",$userid)->where("cust_id",$corpid)->field("sum(buckle_a) as buckle_a")->select();
			 if(empty($month_a)){
			 	$buckle_a = 0;
			 }else{
			 	$buckle_a = $month_a['0']['buckle_a'];
			 } 
			 return $buckle_a+$Recovery_number;			 
	  }	  	  
}