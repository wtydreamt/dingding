<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use think\dingapi\DingCache;

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
	


}
