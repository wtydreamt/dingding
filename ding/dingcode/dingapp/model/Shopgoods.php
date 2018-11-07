<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
class Shopgoods extends Model
{	  
	  protected $table = 'cy_shop_goods';


	  public function GetGoods($page = 0 ){
	  	
	  	     $page = $page*3;
	  	     $corpid  = Session::get("corpid");
	  	     return $list = model("shopgoods")->where("corp_id",$corpid)
	  	     ->where("status",1)
	  	     ->limit($page,3)
	  	     ->field("id,title,desc,price,thumb")
	  	     ->select();

	  }

	  public function GoodsInfo($id){

	  	     $corpid  = Session::get("corpid");
	  	     return $list = model("shopgoods")->where("corp_id",$corpid)
	  	     ->where("id",$id)
	  	     ->where("status",1)
	  	     ->field("id,title,desc,price,thumb,total,is_total")
	  	     ->cache(300)
	  	     ->find();

	  }

}