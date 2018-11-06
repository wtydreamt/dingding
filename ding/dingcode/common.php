<?php
//辅助函数
use think\Session;

function get_corpid(){
    return session::get("corpid");
}

function ReturnJosn($msg,$code,$data){

		 $res = array("errmsg"=>$msg,"errcode"=>$code,"data"=>$data);

		 return json_encode($res,JSON_UNESCAPED_UNICODE);

}