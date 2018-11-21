<?php
//辅助函数
use think\Session;

function get_corpid(){
    return session::get("corpid");
}

function get_userid(){

	$corpid = get_corpid();
	return $userid = Session::get($corpid."userid");

}
function ReturnJosn($msg,$code,$data){

		 $res = array("errmsg"=>$msg,"errcode"=>$code,"data"=>$data);

		 return json_encode($res,JSON_UNESCAPED_UNICODE);

}

function randoms($len = 5,$prefix = "CY_"){

		 $time = date("Y");

		 $str = $prefix;

		 for($i = 0; $i<$len; $i++){
		 	$str = $str.rand(0,9);
		 }

		 return $time."_".$str;
}

function estatus($status_code){

		 if($status_code == "2"){
		 	return "已兑换";
		 }else if($status_code == "1"){
		 	return "已完成";
		 }

}

function getName($arr,$key){

		 if(isset($arr[$key])){
		 	return $arr[$key];
		 }else{
		 	return "悦积分";
		 }

}

function cyabs($number){

		 if(abs($number) == $number){
		 	return "+".$number;
		 }else{
		 	return $number;
		 }

}

function Mytrial_Status($status){
         
         switch ($status) {
         	case '0':
         		return "待初审";
         		break;
         	case '1':
         		return "待终审";
         		break;
         	case '2':
         		return "通过";
         		break;
         	case '3':
         		return "驳回";
         		break;
         	case '10':
         		return "全部";
         		break;          		        		         		         		         	
         	default:
         		return "不存在的状态";
         		break;
         }

}
function splitName($fullname)
{
    $hyphenated = [
        '欧阳',
        '太史',
        '端木',
        '上官',
        '司马',
        '东方',
        '独孤',
        '南宫',
        '万俟',
        '闻人',
        '夏侯',
        '诸葛',
        '尉迟',
        '公羊',
        '赫连',
        '澹台',
        '皇甫',
        '宗政',
        '濮阳',
        '公冶',
        '太叔',
        '申屠',
        '公孙',
        '慕容',
        '仲孙',
        '钟离',
        '长孙',
        '宇文',
        '城池',
        '司徒',
        '鲜于',
        '司空',
        '汝嫣',
        '闾丘',
        '子车',
        '亓官',
        '司寇',
        '巫马',
        '公西',
        '颛孙',
        '壤驷',
        '公良',
        '漆雕',
        '乐正',
        '宰父',
        '谷梁',
        '拓跋',
        '夹谷',
        '轩辕',
        '令狐',
        '段干',
        '百里',
        '呼延',
        '东郭',
        '南门',
        '羊舌',
        '微生',
        '公户',
        '公玉',
        '公仪',
        '梁丘',
        '公仲',
        '公上',
        '公门',
        '公山',
        '公坚',
        '左丘',
        '公伯',
        '西门',
        '公祖',
        '第五',
        '公乘',
        '贯丘',
        '公皙',
        '南荣',
        '东里',
        '东宫',
        '仲长',
        '子书',
        '子桑',
        '即墨',
        '达奚',
        '褚师',
    ];

    $vLength = mb_strlen($fullname, 'utf-8');
    $lastname = '';
    $firstname = '';// 前为姓,后为名

    if ($vLength > 2) {
        $preTwoWords = mb_substr($fullname, 0, 2, 'utf-8');// 取命名的前两个字,看是否在复姓库中
        if (in_array($preTwoWords, $hyphenated)) {
            $lastname = $preTwoWords;
            $firstname = mb_substr($fullname, 2, 10, 'utf-8');
        } else {
            $lastname = mb_substr($fullname, 0, 1, 'utf-8');
            $firstname = mb_substr($fullname, 1, 10, 'utf-8');
        }
    } else {
        if ($vLength == 2) {
            // 全名只有两个字时,以前一个为姓,后一下为名
            $lastname = mb_substr($fullname, 0, 1, 'utf-8');
            $firstname = mb_substr($fullname, 1, 10, 'utf-8');
        } else {
            $lastname = $fullname;
        }
    }

    return [$lastname, $firstname];
}

 function code($code){
       if($code){
        return cyabs($code);
       }
}

function is_follow($create_user_id,$user_id){
         $userid = get_userid();

         if($userid == $create_user_id || in_array($userid,$user_id)){
            return true;
         }else{
            return false;
         }
}

function dateFormat($create_date)
{
    $timestamp = strtotime($create_date);

    return tranTime($timestamp);
}

function tranTime($time)
{
    $rtime = date("m-d H:i",$time);
    $htime = date("H:i",$time);
          
    $time = time() - $time;
          
    if ($time < 60)
    {
        $str = '刚刚';
    }
    elseif ($time < 60 * 60)
    {
        $min = floor($time/60);
        $str = $min.'分钟前';
    }
    elseif ($time < 60 * 60 * 24)
    {
        $h = floor($time/(60*60));
        $str = $h.'小时前 ';
    }
    elseif ($time < 60 * 60 * 24 * 3)
    {
        $d = floor($time/(60*60*24));
        if($d==1)
            $str = '昨天 ';
        else
            $str = '前天 ';
    }
    else
    {
        $str = $rtime;
    }
    return $str;
}          
