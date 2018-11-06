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
}