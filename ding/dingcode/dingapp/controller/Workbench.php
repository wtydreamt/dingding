<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\dingapi\SendMessage;
use think\Session;
use think\Db;
class Workbench extends Common
{
	private $Ding = "";
  private $corpid = "";
  private $userid = "";

	public function __construct(){

		   parent::__construct();
       $this->corpid = session::get("corpid");
       $this->userid = session::get($this->corpid."userid");
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

     //排名
     public function ranking(){
            $corpid = $this->corpid;
            $userid = $this->userid;
            //获取用户累计总积分
            $code_b = model("user")->where("cust_id",$corpid)->where("dd_id",$userid)->field("code_b,dingding_office_id")->find();
            $number = model("Approvalcount")->PersonalMonth();
            if(empty($number)){
                $number['number'] = 0;
            }
            $day_code_b = model("Approvalcount")->GetToday();
            if(!$day_code_b['day_code']){
              $day_code_b = "0";
            }else{
              $day_code_b = $day_code_b['day_code'];
            }
            $office = explode(",",$code_b['dingding_office_id']);
            $office_list = model("Office")->where("cust_id",$this->corpid)->where("dingding_id","in",$office)->field("name,dingding_id")->select();
            return $this->fetch("ranking",["title"=>"积分排名","office"=>$office_list,"number"=>$number['number'],"code_b"=>$code_b['code_b'],"day_code"=>$day_code_b]);
     }

     public function rank($type = "all", $office_id="", $date=""){
            if(!$date){
             $year  = date("Y");
             $month = date("m");               
            }else{
             $date_where = explode("-", $date);
             $year  = $date_where['0'];
             $month = $date_where['1'];
            }
            if(!$office_id){
               $title = "全体";
            }else{
               $office = model("Office")->where("cust_id",$this->corpid)->where("dingding_id",$office_id)->field("name,dingding_id")->find();
               $title = $office['name'];
            }
            $sql = 'SELECT
            u.name AS "name",
            u.office_id,
            u.dd_id,
            u.dingding_office_id,
            IFNULL(
            (e.code_be + e.buckle_be 
            ),
            "0" 
            ) AS balance
            FROM
            sys_user u
            LEFT JOIN ( SELECT * FROM approval_count a WHERE a.cust_id = "'.$this->corpid.'" AND a.`year` = "'.$year.'" AND a.`month` ="'.$month.'") e ON u.dd_id = e.user_id 
            WHERE u.cust_id ="'.$this->corpid.'"';

            if($office_id){
               $sql = $sql.' AND u.office_id = "'.$office_id.'" ORDER BY  balance desc';
            }else{
               $sql = $sql.' ORDER BY  balance desc';
            }  
            $arr = Db::query($sql);
            $rank = "";
            $temp_sort = [];
            $balance   = [];
            $office_ids = "";
            $row = [];
            foreach($arr as $key=>$val){
                 $office_ids = $office_ids . "," .$val['dingding_office_id'];
                 $arr[$key]['dingding_office_id'] =  explode(",", $val['dingding_office_id']);
            }

            $office_ids = trim($office_ids,",");
            $office_ids = array_unique(explode(",", $office_ids));
            $office_list = model("Office")->where("cust_id",$this->corpid)->where("dingding_id","in",$office_ids)->field("name,dingding_id")->select();
            $office_temp = [];
            foreach($office_list as $key=>$val){
                $office_temp[$val['dingding_id']] = $val['name'];
            }
            $office = [];
            foreach($arr as $uk=>$uv){
                $office = "";
                foreach($uv['dingding_office_id'] as $k=>$v){
                    if(isset($office_temp[$v])){
                        $office[$k] = $office_temp[$v]; //获取员工所在的所有部门
                    }
                }
                $arr[$uk]['dingding_office_id'] = $office;
                $temp_sort[$uv['balance']][$uk] = $arr[$uk];
            }
            $i =0;
            foreach($temp_sort as $key=>$val){
                    $i ++;
                    foreach($val as $vk=>$vv){
                    $temp_sort[$key][$vk]['sort'] = $i;
                        if($vv['dd_id'] == $this->userid){
                            $rank = $i;
                        }      
                    }

            }

            $where = json_encode(["office_id"=>$office_id,"date"=>$date,"type"=>$type]);
            return $this->fetch("rank",['title'=>$title."排名","list"=>$temp_sort,"rank"=>$rank,"tname"=>$title,"where"=>$where,"date"=>$date]);
     }
}
