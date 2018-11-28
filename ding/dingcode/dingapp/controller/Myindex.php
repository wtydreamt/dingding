<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;
use app\dingapp\model\Office;
use think\dingapi\DingCache;
use think\Session;

class Myindex extends Common
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
        
    	$user = new User();
    	$userinfo=$user->GetUser();
    	return $this->fetch("index",["title"=>"我的","user"=>$userinfo]);

    }

    //组织架构
    public function framework(){

    	$user = new User();

    	$framework= $user->getframework();
        $corpid = session::get("corpid");
        $config = DingCache::IsvConfig($corpid);
    	return $this->fetch("framework",['title'=>"组织架构","config"=>$config,"user"=>$framework]);

    }

    //我的积分
    public function MyPoints($date = ""){

        if(!$date){
         $year  = date("Y");
         $month = date("m"); 
         $date  = date("Y-m");      
        }else{
         $date_where = explode("-", $date);
         $year  = $date_where['0'];
         $month = $date_where['1'];
        }
        $corpid = session::get("corpid");
        $userid = session::get($corpid."userid");
        $fixed_value= model("User")->where("dd_id",$userid)->where("cust_id",$corpid)->field("fixed_value")->find(); //获取员工固定分
        //获取员当月工奖扣分数 C分 和 福分
        $points = model("Approvalcount")
        ->where("user_id",$userid)
        ->where("cust_id",$corpid)
        ->where("year",$year)
        ->where("month",$month)
        ->field("code_a,buckle_a,code_be,buckle_be,code_c,buckle_c")->select();
        $points = collection($points)->toArray();
        $g = ($fixed_value['fixed_value'])?$fixed_value['fixed_value']:0;

        if(empty($points)){
            $c = "0";
            $d = "0";
            $f = "0";
            $z = "0"+$g;
        }else{
            $c = $points['0']['code_c']+$points['0']['buckle_c'];
            $d = $points['0']['code_be']+$points['0']['buckle_be'];
            $f = $points['0']['code_a']+$points['0']['buckle_a'];
            $z = $points['0']['code_be']+$points['0']['buckle_be']+$g;
        }
        $arr = array("c"=>$c,"g"=>$g,"d"=>$d,"f"=>$f,"z"=>$z);
        return $this->fetch("mypoints",['title'=>"我的积分","arr"=>$arr,"date"=>$date]);
    }

    //奖扣明细 D 分 C分 福分 统计
    public function DetailedD($type = "D", $date = "", $status = "10"){
        if(!$date){
           $date = date("Y-m");
        }
        if($type !="F"){
        $data = model("Fabulous")->AdDetailed($type,$date,$status);
        $Detailed = $data['0'];
        $user_list= $data['1'];            
        }     

        $corpid = session::get("corpid");
        $userid = session::get($corpid."userid");
        if($type == "C"){
          $code_c = model("Approvalcount")->where("user_id",$userid)->where("cust_id",$corpid)->field("code_c,buckle_c")->select();
          $code_c = collection($code_c)->toArray();
          
          if(empty($code_c)){
            $reduce = "0";
            $plus   = "0";
          }else{
            $reduce = $code_c['0']['buckle_c'];
            $plus   = $code_c['0']['code_c'];
          }
          return $this->fetch("detailec",['title'=>"我的C分","list"=>$Detailed,"user"=>$user_list,"date"=>$date,"status"=>$status,"reduce"=>$reduce,"plus"=>$plus]);

        }else if($type == "D"){

          return $this->fetch("detailed",['title'=>"我的奖扣分","list"=>$Detailed,"user"=>$user_list,"date"=>$date,"status"=>$status]);

        }else if($type == "F"){

           $income = model("Approvald")->income();  //统计福分收入
           $expenditure = model("Approvald")->expenditure();

           if($status == "10"){
              $type = "ALL";
           }else if($status == "1"){
              $type = "S";
           }else if($status == "2"){
              $type = "Z";
           }

           $list = model("Approvald")->Blessings($date, $type);

           $balance = model("user")->where("dd_id",$userid)->where("cust_id",$corpid)->field("balance")->find();

           return $this->fetch("detailef",
                              ['title'=>"我的福分",
                               "share_date" =>$date,
                               "status"=>$status,
                               "income"=>$income,
                               "expenditure"=>$expenditure,
                               "balance"=>$balance['balance']*100,
                               "list"=>$list['0'],
                               "user"=>$list['1'],
                              ]
                              ); 
        }
        
    }



}
