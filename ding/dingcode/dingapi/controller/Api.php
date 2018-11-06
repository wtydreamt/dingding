<?php
namespace app\dingapi\controller;
use think\Controller;
use think\dingapi\DingCache;
use think\dingapi\SinceDing;
Class Api extends Controller{

	  private $Ding = "";

	  public function __construct(){
	  	     parent::__construct();
	  }

      public function index($corpid){
      	     $config = DingCache::IsvConfig($corpid); //jsapi 鉴权
      	     return $this->fetch("index",['corpid'=>$corpid,"config"=>$config]);
                 

      }

	public function getuser($corpid="" ,$code="" ){
		     $user=DingCache::get_user($corpid,$code);
		     $user=json_decode($user,true);
                
		     echo DingCache::get_user_info($corpid,$user['userid']);
	}  

}
