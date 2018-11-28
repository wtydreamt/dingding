<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\dingapi\SendMessage;
use think\Request;
use think\Session;
use think\view;
class Buckle extends Common
{
  private $corpid = "";
  private $userid = "";

  public function __construct(){
       parent::__construct();
       $this->corpid = session::get("corpid");
       $this->userid = session::get($this->corpid."userid");
  }

    /**
     * [index 奖扣事件入口]
     * @return [type] [description]
     */
    public function index()
    {		

          //获取jsapi鉴权信息
    	    $config = DingCache::IsvConfig($this->corpid);
          //获取用户信息
          $user = model("User")->GetUser();
          $userinfo = json_encode($user);
          //获取一级事件库
          $Events_list=model("Events")->GetEvents($type = "0",$parent = "0");
          $view = new View();
          $view->title = "奖扣申请";
          $view->config = $config;
          $view->userinfo = $userinfo;
          $view->Events_list = $Events_list;
          return $view->fetch();  
    }

    //奖扣数据插入
    public function BuckleInsert(){

          $data = Request::instance()->param();
          //组装奖扣申请信息
          $app_data = [];
          $app_data['status']         = 0;   //审核状态初值为0 待审核
          $app_data['corp_id']        = $this->corpid;
          $app_data['end_user_id']    = $data['end_user'];
          $app_data['create_date']    = date('Y-m-d H:i:s');           
          $app_data['first_user_id']  = $data['first_user'];
          $app_data['create_user_id'] = $this->userid;

          //奖扣事件信息
          $event_data = [];
          $event_data['date']        = $data['apply_date'];
          $event_data['corp_id']     = $this->corpid;
          $event_data['event_name']  = $data['first'] . ',' . $data['thing'];
          $event_data['event_desc']  = $data['desc'];
          $event_data['create_time'] = date('Y-m-d H:i:s'); 

          //获取奖扣对象
          $userlist = json_decode($data['userlist'],true);

          //获取奖扣分数
          $number   = [];
          if($data['is_c']){
            $number['code_c'] = ($data['is_deduct'] ? '-' : '') . $data['apply_code_c'];
            $number['code_b'] = ($data['is_deduct'] ? '-' : '') . $data['apply_code_b'];            
          }else{
            $number['code_c'] = 0;
            $number['code_b'] = ($data['is_deduct'] ? '-' : '').$data['apply_code_b'];            
          }
          echo model("Approvale")->BuckleInsert($app_data,$event_data,$userlist,$number);


    }

    //发送工作消息通知
    public function Sendmsg(){

           $user = model("User")->GetUser();
           $data = Request::instance()->param();
           //检查奖扣类型 和 分数类型
           if($data['is_deduct']){
              if($data['is_c'] == 1){
                 $event_data = "D分："."-".$data['apply_code_b'].","."C分："."-".$data['apply_code_c'];
              }else{
                 $event_data = "D分："."-".$data['apply_code_b'];
              }
           }else{
              if($data['is_c'] == 1){
                 $event_data = "D分：".$data['apply_code_b'].","."C分：".$data['apply_code_c'];
              }else{
                 $event_data = "D分：".$data['apply_code_b'];
              }
           }
           $event_user_name = json_decode($data['event_user_name'],true);
           $userlist = [];

           foreach($event_user_name as $key=>$val){
                   $userlist[$key] = $val['name']; 
           }

           $userstring = implode(",", $userlist);
           $json_data = SendMessage::MessageData($this->corpid,$user['name']
            ,$data['event_name']
            ,$userstring
            ,$data['first_user_name']
            ,$data['end_user_name']
            ,$data['user_object']
            ,$event_data,"待初审");
            echo $res = SendMessage::SendWorkmsg($this->corpid,$json_data);  
    }

    //发送群消息
    public function sendCath(){
           $json_data = SendMessage::chatdata($this->corpid,$user['name']
            ,$data['event_name']
            ,$userstring
            ,$data['first_user_name']
            ,$data['end_user_name']
            ,$data['user_object']
            ,$event_data,"待初审");
            echo $res = SendMessage::Sendchat($this->corpid,$json_data); 
    }

    public function get_events($id = "",$name = ""){
           $Events_list=model("Events")->GetEvents($type = "0" ,$id ,$name);
           echo ReturnJosn("ok","0",$Events_list);
    }

    public function get_events_info($id = ""){
           $Events_info = model("Events")->get_event_info($id);
           echo ReturnJosn("ok","0",$Events_info);

    }

    //奖扣列表
    public function award_list($page=1, $time ="", $status="10", $method = ""){
           $number = ($page-1) * 10;
           $list   = model("Approvald")->award_list($number, $time, $status);
           $number = count($list);

           if(!$method){
           return $this->fetch("award_list",['title'=>"奖扣列表","list"=>$list,"page"=>$page,"time"=>$time,"status"=>$status,"number"=>$number]);
           }else{
           echo ReturnJosn("ok","0",array("list"=>$list,"num"=>$number,"page"=>$page));
           }
    }

    public function award_details($id = 0){
           $data = model("Approvald")->award_details($id);
           return $this->fetch("award_details",["title"=>"奖扣详情","apply"=>$data['apply'],"prize_winner"=>$data['prize_winner'],"username"=>$data['user_name']]);
    } 

}
