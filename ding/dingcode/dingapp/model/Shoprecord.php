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

}