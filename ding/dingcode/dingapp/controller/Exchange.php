<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;
use think\view;
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

    //兑换记录
    public function record(){
    	return $this->fetch("record",["title"=>"兑换记录"]);
    }
}
