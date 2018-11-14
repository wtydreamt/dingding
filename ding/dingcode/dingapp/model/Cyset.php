<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;

class Cyset extends Model
{	  
	  protected $table = 'sys_set';
	  
	  public function get_sys_set(){

	  		 $corpid = Session::get("corpid");
	  	     //获取点赞配置信息
	  		 return model("Cyset")->where("corp_id",$corpid)->field("is_auto,quota_c,likes_quota_c")->find();

	  }
	  
}