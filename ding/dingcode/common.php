<?php
//辅助函数
use think\Session;

function get_corpid(){
    return session::get("corpid");
}

function get_userid(){

	$corpid = get_corpid();
	return $userid = Session::get($corpid."userid");

}
function ReturnJosn($msg,$code,$data){

		 $res = array("errmsg"=>$msg,"errcode"=>$code,"data"=>$data);

		 return json_encode($res,JSON_UNESCAPED_UNICODE);

}

function randoms($len = 5,$prefix = "CY_"){

		 $time = date("Y");

		 $str = $prefix;

		 for($i = 0; $i<$len; $i++){
		 	$str = $str.rand(0,9);
		 }

		 return $time."_".$str;
}

function estatus($status_code){

		 if($status_code == "2"){
		 	return "已兑换";
		 }else if($status_code == "1"){
		 	return "已完成";
		 }

}