<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
use app\dingapp\model\Office;

class Events extends Model
{	  
	  protected $table = 'cy_events';

	  //获取事件
	  public function GetEvents($type = 1,$parent = "0", $name = ""){

	  	     $corpid = session::get("corpid");

	  	     $events = model("Events")->where("corp_id",$corpid)->where("is_label",$type)->where("is_month",0)->where("parent_id",$parent);

	  	     if(!$name){
	  	     	$events_list=$events->field("id,name")->select();
	  	     }else{
	  	     	$events_list=$events->where("name","like","'".$name."%'")->field("id,name")->select();
	  	     }

	  	     return $events_list;

	  }

	  //获取事件详情
	  public function get_event_info($id){

	  	     $events_info = $this->where("is_month",0)->where("is_label",0)->where("id",$id)
	  	                    ->field("min_a,max_a,min_b,max_b")->find();

            if ($events_info['min_b'] == abs($events_info['min_b'])) {
                $events_info['is_deduct'] = 0;
            } else {
                $events_info['is_deduct'] = 1;
            }

	  	    return $events_info;
	  }
}