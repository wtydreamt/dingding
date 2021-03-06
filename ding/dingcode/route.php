<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::get("","dingapp/index/index");
Route::get("test","dingapp/index/test");
Route::get("home","dingapp/index/home");
Route::rule("getuser","dingapp/index/getuser");              //授权获取用户信息
Route::rule("getEvent","dingapp/index/getEvent");            //HomegetEvent
//我的模块
Route::rule("myindex","dingapp/Myindex/index");          //我的个人中心
Route::rule("framework","dingapp/Myindex/framework");    //组织架构
Route::rule("MyPoints","dingapp/Myindex/MyPoints");      //我的积分
Route::rule("DetailedD","dingapp/Myindex/DetailedD");    //我的奖扣分数D分

//赞赏
Route::rule("fabulous","dingapp/Fabulous/index");     
Route::rule("insertaaa","dingapp/Fabulous/insertaaa");
Route::rule("follow","dingapp/Fabulous/follow");        //跟赞   


//工作台
Route::rule("workbench","dingapp/Workbench/index"); //工作台
Route::rule("ranking","dingapp/Workbench/ranking"); //积分排名
Route::rule("rank","dingapp/Workbench/rank");

Route::rule("mytrial","dingapp/Workbench/mytrial"); //我的审判
Route::rule("Approval_detail","dingapp/Workbench/Approval_detail"); //审核细节
Route::rule("isadopt","dingapp/Workbench/Approval_is_adopt");       //审核是否通过


//同步组织架构
Route::rule("sync","dingapp/index/sync_framework"); 


//奖扣
Route::rule("buckle","dingapp/buckle/index");                //奖扣申请
Route::rule("get_events","dingapp/buckle/get_events");       //获取事件
Route::rule("events_info","dingapp/buckle/get_events_info"); //获取事件详情
Route::rule("BuckleInsert","dingapp/buckle/BuckleInsert");   //事件数据插入 
Route::rule("award_list","dingapp/buckle/award_list");       //奖扣记录
Route::rule("award_details","dingapp/buckle/award_details"); //奖扣详情


//消息通知
Route::rule("Sendmsg","dingapp/buckle/Sendmsg");



//兑换模块
Route::rule("exchange","dingapp/Exchange/index");   //兑换中心
Route::rule("exchange/info","dingapp/Exchange/info"); //商品兑换详情
Route::rule("exchange/record","dingapp/Exchange/record"); //兑换记录
Route::rule("testing","dingapp/Exchange/testing"); //兑换积分检查

Route::rule("inserrecord","dingapp/Exchange/inserrecord");

//接口路由
Route::rule("api/corpid","dingapi/api/index");
Route::rule("api/getuser","dingapi/api/getuser");