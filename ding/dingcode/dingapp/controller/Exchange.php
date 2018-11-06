<?php
namespace app\dingapp\controller;
use app\common\controller\Common;
use app\dingapp\model\User;

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
    public function index()
    {		
    	return $this->fetch("index",["title"=>"兑换中心"]);
    }


    public function info(){
    	return $this->fetch("info",["title"=>"兑换中心"]);
    }

    //兑换记录
    public function record(){
    	return $this->fetch("record",["title"=>"兑换记录"]);
    }
}
