<?php
namespace app\dingapi\model;

use think\Model;
use think\Db;
use think\Config;

class Auth extends Model
{
	public static function auth_info($corpid,$biz_id){
		   $sql = "select biz_data from open_sync_biz_data where biz_type = 4 AND biz_id = '".$biz_id."' AND `corp_id`= '".$corpid."' ";
		   $db  =  config::get("ding");
		   $data=Db::connect($db)->query($sql);

		   return json_decode($data['0']['biz_data'],true);
	}
	public static function suiteTicket($biz_id){
		   $sql = "select biz_data from open_sync_biz_data where biz_type = 2 AND biz_id = '".$biz_id."' ";
		   $db  =  config::get("ding");
		   $data=Db::connect($db)->query($sql);

		   return $data;
	}

	public function sync($biz_id,$id){

		   $sql = "select biz_data,corp_id,biz_type,biz_id from open_sync_biz_data_medium where id = '".$id."' AND subscribe_id = '".$biz_id."' ";
		   $db  =  config::get("ding");
		   $data=Db::connect($db)->query($sql);	
		   return $data['0'];
	}	
}