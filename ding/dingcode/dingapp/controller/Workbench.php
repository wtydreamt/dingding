<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\session;

class Workbench extends Common
{
	private $Ding = "";

	public function __construct(){
		   parent::__construct();
	}

	/**
	 * [index 入口方法]
	 * @param  [string] $corpid [企业corpid]
	 */
    public function index()
    {		
    	return $this->fetch("index",["title"=>"工作台"]);
    }
	
	//我的审核
    public function MyTrial($First_trial = 0, $date = 0, $status = 10){
    	$data = model("Fabulous")->Myreview($First_trial, $date, $status);
    	$data = collection($data)->toArray();
    	if($First_trial){
    	   $trial['data'] = "MY_End";
    	   $trial['data_val'] = "我的终审";
    	}else{
    	   $trial['data'] = "MY_First";
    	   $trial['data_val'] = "我的初审";    		
    	}
    	return $this->fetch("mytrial",["title"=>"我的审核","data_list"=>$data,"First_trial"=>$trial,"date"=>$date,"status"=>$status]);
    }

    //奖扣事件细节查看
    public function Approval_detail($id = 0, $trial = ""){

        $data = model("Fabulous")->Approval_detail($id, $trial);
        $corpid = session::get("corpid");
        $userid = session::get($corpid."userid");
        $config = DingCache::IsvConfig($corpid);
        return $this->fetch("detail",['config'=>$config,'userid'=>$userid,'title'=>"我的审核","data"=>$data['info'],"user"=>$data['user'],"userlist"=>$data['userlist']]);

    }
     
     /**
      * [Approval_is_adopt 奖扣事件申请是否通过]
      * @param [type] $id     [事件处理id]
      * @param [type] $desc   [审批意见]
      * @param [type] $status [审批状态]
      * @param [type] $type   [初审 或 终审]
      */
     public function Approval_is_adopt($id, $remark, $status, $type){
            
            model("Fabulous")->is_adopt($id, $remark, $status, $type);die;

     }
}
