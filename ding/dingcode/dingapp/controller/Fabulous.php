<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;
use think\view;
class Fabulous extends Common
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
          $view = new View();
          $view->title = "赞赏";
          return $view->fetch();  
    }



}
