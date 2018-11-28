<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;
use think\view;
use think\Session;
class Exchange extends Common
{
	private $Ding = "";

	public function __construct(){
		   parent::__construct();
	}

	/**
	 * [index 入口方法]
	 * @param  [string] $corpid [企业corpid]
	 */
    public function index($page = 0,$is_ajax = 0)
    {		
        $this->view->engine->layout(false);
        $goodslist=model("shopgoods")->GetGoods($page);
        if($is_ajax){
          if(empty($goodslist)){
            echo ReturnJosn("ok","D_400","");die;
          }else{
            echo ReturnJosn("ok","D_200",$goodslist);die;
          }
        }else{
          $view = new View();
          $view->title = "兑换中心";
          $view->list = $goodslist;
          return $view->fetch();            
        }
    }


    public function info($id = ""){

        $this->view->engine->layout(false); 
        $info = model("shopgoods")->GoodsInfo($id);
        $view = new View();
        $view->title = "兑换中心";
        $view->info = $info;
        return $view->fetch();

    }

    public function testing($id,$num){
           $corpid = session::get("corpid");
           $userid = session::get($corpid."userid");
           $goods = model("Shopgoods")->check($id);
           //商品不存在
           if(!$goods){
            echo   ReturnJosn("G_no","400","");die;
           }

           $count_balance = $num * $goods['price'];

           if($goods['total'] < $num){
            echo   ReturnJosn("G_ok","404",array("balance"=>$count_balance,"msg"=>"商品库存不足"));die;
           }
           $user  = model("user")->where("cust_id",$corpid)->where("dd_id",$userid)->field("balance")->find();

           if($user['balance']*100 < $count_balance){
            echo   ReturnJosn("G_ok","405",array("balance"=>$count_balance,"msg"=>"账户福分不足"));die;
           }
           
           $Account=model("Account")->CheckBalance();

           if($Account['balance']*100 > $count_balance){
             echo   ReturnJosn("G_ok","200",array("balance"=>$count_balance,"msg"=>"true"));die;
           }else{
             echo   ReturnJosn("G_ok","406",array("balance"=>$count_balance,"msg"=>"公司账户余额不足"));die;
           }

    }

    public function inserrecord($id,$num){
           $goods=model("Shoprecord")->shop_record($id,$num);

           echo $goods;

    }

    //兑换记录
    public function record(){

      $record = model("Shoprecord")->recordlist();
    	return $this->fetch("record",["title"=>"兑换记录","record"=>$record]);

    }
}
