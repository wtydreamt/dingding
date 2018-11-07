<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
use think\dingapi\DingCache;
class Office extends Model
{   
    protected $table = 'dingding_office';
	  public function Framework(){

	  		 $cust_id = session::get("corpid");
	  		 $userid  = session::get($cust_id."userid");
	  		 $list    = DingCache::get_department_list($cust_id);
	  		 $data    = json_decode($list,true);

	  		 if($data['errmsg'] == "ok"){

	  		 	 $department = $data['department'];

                                 $updatelist = [];
                                 $inserlist  = [];
                                 $userlit    = [];
                                 foreach($department as $key=>$val){

                                 	$dingding_id = $val['id']; //钉钉部门id

                                  $list=DingCache::get_department_user($cust_id,$dingding_id);
                                  
                                  $user = json_decode($list,true);

                                  if($user['errmsg'] == "ok"){
                                    $userlit[$dingding_id]   = $user;
                                  }

                                 	$res_list = Db::table("dingding_office")->where("cust_id", $cust_id)->where("dingding_id",$dingding_id)->field('id')->find();

                                 	if($res_list){
                                     //组织架构存在进行更新
                                 	   $updatelist[$key]['id']      = $res_list['id']; 
                                     $updatelist[$key]['name']    = $val['name'];
                                     $updatelist[$key]['cust_id'] = $cust_id;      
                                     $updatelist[$key]['dingding_id'] = $dingding_id;          	   
                                 	   $updatelist[$key]['create_date'] = date("Y-m-d H:i:s");
                                 	   $updatelist[$key]['parent_id']   = isset($val['parentid'])?$val['parentid']:"0";
                                 	   
                                 	}else{
                                     //组织架构进行同步
                                     $inserlist[$key]['parent_id']   = isset($val['parentid'])?$val['parentid']:"0";
                                 	   $inserlist[$key]['create_date'] = date("Y-m-d H:i:s");
                                 	   $inserlist[$key]['dingding_id'] = $dingding_id;
                                 	   $inserlist[$key]['cust_id']     = $cust_id;
                                 	   $inserlist[$key]['name']	   = $val['name'];

                                 	}
                                 }
                                 
                                 if(!empty($inserlist)){
                                 	$res=model("Office")->saveAll($inserlist, false);
                                 }
                                 if(!empty($updatelist)){
                                 	$res=model("Office")->saveAll($updatelist);
                                 }
                                 $this->updateuser($userlit);

	  		 }else{
	  		 	 echo $list;
	  		 }
	  }

          public function updateuser($userlit){

                 $cust_id = session::get("corpid");
                 $insertuser = [];
                 $updateuser = [];
                 $i = 0;
                 foreach($userlit as $key=>$val){
                 
                     if(!empty($val['userlist'])){

                           foreach($val['userlist'] as $ukev=>$uval){
                               $i ++;
                               $res_list= Db::table("sys_user")
                                        ->where("cust_id", $cust_id)->where("dd_id",$uval['userid'])
                                        ->field('id,office_id')->find();
                                        if($res_list){
                                          $updateuser[$i]['dd_id']              = $uval['userid'];
                                          $updateuser[$i]['cust_id']            = $cust_id;
                                          $updateuser[$i]['id']                 = $res_list['id'];
                                          $updateuser[$i]['name']               = $uval['name'];
                                          $updateuser[$i]['is_dd_admin']        = ($uval['isAdmin'])?"1":0;
                                          $updateuser[$i]['position']           = isset($uval['position'])?$uval['position']:"";
                                          $updateuser[$i]['office_id']          = $key;
                                          $updateuser[$i]['dd_avatar']          = $uval['avatar'];
                                          $updateuser[$i]['update_date']        = date("Y-m-d H:i:s");
                                          $updateuser[$i]['dingding_office_id'] = implode($uval['department'],",");                                          
                                        }else{
                                          $insertuser[$i]['dd_id']              = $uval['userid'];
                                          $insertuser[$i]['cust_id']            = $cust_id;
                                          $insertuser[$i]['name']               = $uval['name'];
                                          $insertuser[$i]['is_dd_admin']        = ($uval['isAdmin'])?"1":0;
                                          $insertuser[$i]['position']           = isset($uval['position'])?$uval['position']:"";
                                          $insertuser[$i]['office_id']          = $key;
                                          $insertuser[$i]['dd_avatar']          = $uval['avatar'];
                                          $insertuser[$i]['create_date']        = date("Y-m-d H:i:s");
                                          $insertuser[$i]['dingding_office_id'] = implode($uval['department'],",");
                                        }
                           }

                     }
                 }
                                 if(!empty($insertuser)){
                                  $res=model("user")->saveAll($insertuser);
                                 }
                                 if(!empty($updateuser)){
                                  $res=model("user")->saveAll($updateuser);
                                 }                 

          }
}