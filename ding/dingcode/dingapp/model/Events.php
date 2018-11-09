<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
use app\dingapp\model\Office;

class Events extends Model
{	  
	  protected $table = 'cy_events';

	  //获取点赞标签
	  public function GetEvents(){

	  	     $corpid = session::get("corpid");

	  	     $events_list=model("Events")->where("corp_id",$corpid)->where("is_label",1)->field("id,name")->cache(7200)->select();

	  	     return $events_list;
	  }
}