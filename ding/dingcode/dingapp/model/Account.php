<?php
namespace app\dingapp\model;
use think\Session;
use think\Model;
use think\Db;
use app\dingapi\model\Auth;

class Account extends Model
{	  
	  protected $table = 'cy_account';
      

      public function insert_account(){

      	     $corpid = session::get("corpid");

      	     $data=Auth::auth_info($corpid,"4243001");
      	     $auth_corp_info = $data['auth_corp_info'];

      	     $corpid =  $auth_corp_info['corpid'];

      	     $this->custinfo($corpid,$data);

			 try {
	      	     //查看企业是否存在账户
	      	     $res=model("account")->where("custid",$corpid)->field("acctName,acctNo")->find();

	      	     if($res){
	      	          //存在进行修改
	      	          $data = ['acctName'=>$auth_corp_info['corp_name'],"updTime"=>date("Y-m-d H:i:s")];
	      	          $res_no = model("account")->save($data,["acctNo"=>$res['acctNo']]);
	      	     }else{
	      	     	  $acctNo = $this->randoms();
	      	     	  $data = ['acctName'=>$auth_corp_info['corp_name'],"createTime"=>date("Y-m-d H:i:s"),"acctNo"=>$acctNo,"custId"=>$corpid];
	      	     	  model("account")->save($data);
	      	     	  $res_no = model("account")->acctNo;
	      	     }

			 } catch (Exception $e) {
				 print $e->getMessage();
				 exit();				 
			 }
      }

      public function custinfo($corpid,$data){

             $auth_info      = $data['auth_info'];
             $auth_corp_info = $data['auth_corp_info'];
             $res = model("Custinfo")->where("custId",$corpid)->field("custId")->find();

             if(!$res){
             	try {
	             	$data = ['custId'=>$corpid,"agent_id"=>$auth_info['agent']['0']['agentid'],"custName"=>$auth_corp_info['corp_name'],"createTime"=>date("Y-m-d H:i:s")];
	             	model("Custinfo")->save($data);             		
             	} catch (Exception $e) {
					 print $e->getMessage();
					 exit();             		
             	}

             }

      }

      //公司员工福分总余额
	  public function CheckBalance(){

	  		 $corpid = session::get("corpid");
	  		 
	  		 return model("Account")->where("custId",$corpid)->field("balance")->find();
	  		 
	  }

	    public function randoms(){
		      	//不存在进行创建
		      	$acctNo = randoms($len = 5,$prefix = "CY_");
		        $res_no = model("account")->where("acctNo",$acctNo)->field("acctNo")->find();

		        if($res_no){
		           $this->randoms();
		        }else{
		           return $acctNo;
		        } 

	    }

}