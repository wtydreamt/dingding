<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;
use think\Request;
use think\Session;
use think\view;
class Fabulous extends Common
{
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

    	    $config = DingCache::IsvConfig($this->corpid);
          $list   = model("Events")->GetEvents();
          //获取点赞配置信息
          $FabulousSet = model("Cyset")->get_sys_set();
          $user = model("User")->GetUser();
          $view = new View();
          $view->title = "赞赏";
          $view->config = $config;
          $view->list   = $list;
          $view->FabulousSet = $FabulousSet['quota_c'];
          $view->balance= $user['balance']*100;
          $view->user_id = json_encode(array("userid"=>$user['dd_id'],"balance"=>$user['balance']*100,"FabulousSet"=>$FabulousSet['quota_c']));
          return $view->fetch();  

    }
    //记录点赞事件
    public function insertaaa($label,$desc,$code,$apply_user_id){
           $datetime = date("Y-m-d H:i:s");
           $app_arr = [];
           $appevent_arr = [];
           //事件申请数据
           $app_arr['status']         = 2;
           $app_arr['corp_id']        = $this->corpid;
           $app_arr['create_user_id'] = $this->userid;
           $app_arr['create_date']    = $datetime;
           //事件记录数据
           $appevent_arr['corp_id']    = $this->corpid;
           $appevent_arr['event_name'] = '赞赏';
           $appevent_arr['label']      = $label;
           $appevent_arr['event_desc'] = $desc;
           $appevent_arr['is_a_or_b']  = 1;
           $appevent_arr['is_likes']   = 1;
           $appevent_arr['date'] = date("Y-m-d");
           $appevent_arr['create_time'] = $datetime;
           $res_app=model("Fabulous")->approval($app_arr,$appevent_arr,$apply_user_id,$code);
           $url = "/home?dd_nav_bgcolor=FF5E97F6";
           header("Location:$url");
    }

    //跟赞
    public function follow(){
      
           $request = Request::instance();
           $method  = $request->method();
           $FabulousSet = model("Cyset")->get_sys_set();
           if($method == "GET"){
              $id      = intval($request->param("id"));
              $like_id = intval($request->param("like_id"));
              $user    = model("Approvald")->alias("d")
                ->join("sys_user u","d.user_id = u.dd_id")
                ->where("d.id",$id)->where("d.cust_id",$this->corpid)->where("u.cust_id",$this->corpid)->field("name,user_id")->find();

              return $this->fetch("follow",['title'=>'悦积分','like_name_id'=>$user['user_id'],'like_id'=>$like_id,"likes_quota_c"=>$FabulousSet['likes_quota_c'],"name"=>$user['name']]); 

           }else if($method == "POST"){
              $data = $request->param();
              $datetime = date("Y-m-d H:i:s");
              $app_arr = [];
              $appevent_arr = [];
             //事件申请数据
              $app_arr['status']         = 2;
              $app_arr['corp_id']        = $this->corpid;
              $app_arr['create_user_id'] = $this->userid;
              $app_arr['create_date']    = $datetime;
             //事件记录数据
              $appevent_arr['corp_id']    = $this->corpid;
              $appevent_arr['event_name'] = '跟赞';
              $appevent_arr['event_desc'] = ($data['desc'])?$data['desc']:"一赞增币";
              $appevent_arr['is_a_or_b']  = 1;
              $appevent_arr['is_likes']   = 2;
              $appevent_arr['like_id']    = $data['like_id'];
              $appevent_arr['date'] = date("Y-m-d");
              $appevent_arr['create_time'] = $datetime;
              $res_app=model("Fabulous")->approval($app_arr,$appevent_arr,$data['like_name_id'],$FabulousSet['likes_quota_c']);
              $url = "/home?dd_nav_bgcolor=FF5E97F6";
              header("Location:$url");
           }


    }

}
