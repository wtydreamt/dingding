<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
class Shoprecord extends Model
{	  
	  protected $table = 'cy_shop_record';

	  public function shop_record($id,$num){

	  	    $corpid = session::get("corpid");
	  	    $userid = session::get($corpid."userid");
	  	    $user   = model("user")->where("cust_id",$corpid)->where("dd_id",$userid)->field("office_id,balance")->find();
			$goods = model("shopgoods")->where("corp_id",$corpid)
	  	    ->where("id",$id)
	  	    ->field("id,title,desc,price,thumb,total,is_total")
	  	    ->find();
	  	    $code = $this->card();	 

	  	    $data = [
	  	    	     "code"     =>  $code,
	  	             "status"   =>  2,
	  	             "number"   =>  $num,	  	    	     
	  	             "img_url"  =>  $goods['thumb'],
	  	             "corp_id"  =>  $corpid,
	  	             "user_id"  =>  $userid,
	  	             "goods_id"	=>  $goods['id'],  	             
	  	             "integral" =>  $num*$goods['price'],
	  	             "office_id"=>  $user['office_id'],
	  	             "create_time"=>date("Y-m-d H:i:s"),
	  	             "g_intergral"=>$goods['price'],
	  	             "goods_name" =>$goods['title']
	  	             ];

	  	    $res=model("Shoprecord")->save($data);
            
	  	    if(model("Shoprecord")->id){

	  	    	 $balance = $user['balance'] - $data['integral']/100;
	  	    	 $total = $goods['total'] - $num;

	  	    	 model("Shopgoods")->save(['total'=>$total],['id'=>$id]);
	  	    	 Db::table('cy_shop_goods')->cache('goods_info')->update(['id'=>$id,'total'=>$total]); //商品库存扣减缓存更新

	  	    	 $user_res=model("user")->save(['balance'=>$balance],['dd_id'=>$userid,"cust_id"=>$corpid]); //扣减员工可用余额
	  	    	 model("Approvalcount")->CheckCount($userid,$corpid,['buckle_a'=>"-".$num*$goods['price']]); //记录当月福分消耗情况
	  	    	 if($user_res){
	  	    	 	$Account  = model("Account")->where("custId",$corpid)->field("balance")->find(); 
	  	    	 	$a_blance = $Account['balance'] - $data['integral']/100;
	  	    	 	$res_a = model("Account")->save(['balance'=>$a_blance],['custId'=>$corpid]); //扣减公司账户员工可用余额

	  	    	 	if($res_a){
	  	    	 	   return ReturnJosn("G_ok","200",array("balance"=>"","msg"=>"true"));die;
	  	    	 	}else{
	  	    	 	   return ReturnJosn("G_ok","204",array("balance"=>"商品兑换失败稍后再试","msg"=>"true"));die;
	  	    	 	}
	  	    	 	
	  	    	 }else{
	  	    	    return ReturnJosn("G_ok","204",array("balance"=>"商品兑换失败稍后再试","msg"=>"true"));die;
	  	    	 }

	  	    }else{
	  	    	    return ReturnJosn("G_ok","204",array("balance"=>"商品兑换失败稍后再试","msg"=>"true"));die;
	  	    }
	  }

	  public function card(){

	  	     $str=randoms($len = 6,$prefix = "CARD_");

	  	     $res=model("Shoprecord")->where("code",$str)->field("id")->find();

	  	     if($res){
	  	     	$this->card();
	  	     }else{
	  	     	return $str;
	  	     }
	  }


	  //获取企业员工兑换记录
	  public function recordlist(){

	  	     $corpid = Session::get("corpid");
	  	     $userid = Session::get($corpid."userid");

	  	     return  model("Shoprecord")->where("corp_id",$corpid)->where("user_id",$userid)
	  	                        ->field("goods_name,img_url,status,create_time,integral")
	  	                        ->select();

	  }

	  //商品消费福分 DATE_FORMAT(c.create_time,'%Y-%m-%d') as dates
	  public function consumption($userid,$corpid,$date){
	  	     //福分商品消费记录统计
	  		 $shop = model("User")->alias("u")->join("cy_shop_record c","u.dd_id = c.user_id")
	  		 ->where("c.user_id",$userid)->where("c.corp_id",$corpid)
	  		 ->where("u.dd_id",$userid)->where("u.cust_id",$corpid)
	  		 ->where('DATE_FORMAT(c.create_time,"%Y-%m") ='."'$date'")
	  		 ->field("'X-T' as create_user_id,'X-T' as user_id,c.integral as code_a,c.create_time as create_date, '商品兑换' as event_name, '1' as status_code")
	  		 ->select();	
	  		 return $shop = collection($shop)->toArray();  	
	  }
}