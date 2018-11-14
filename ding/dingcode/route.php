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
Route::rule("getuser","dingapp/index/getuser");     //授权获取用户信息

//我的模块
Route::rule("myindex","dingapp/Myindex/index");     //我的个人中心
Route::rule("framework","dingapp/Myindex/framework");     //组织架构

//赞赏
Route::rule("fabulous","dingapp/Fabulous/index");     
Route::rule("insertaaa","dingapp/Fabulous/insertaaa");

//工作台
Route::rule("workbench","dingapp/Workbench/index"); //工作台


//奖扣
Route::rule("buckle","dingapp/buckle/index"); //奖扣申请
Route::rule("get_events","dingapp/buckle/get_events"); //获取事件
Route::rule("events_info","dingapp/buckle/get_events_info");

//兑换模块
Route::rule("exchange","dingapp/Exchange/index");   //兑换中心
Route::rule("exchange/info","dingapp/Exchange/info"); //商品兑换详情
Route::rule("exchange/record","dingapp/Exchange/record"); //兑换记录
Route::rule("testing","dingapp/Exchange/testing"); //兑换积分检查

Route::rule("inserrecord","dingapp/Exchange/inserrecord");

//接口路由
Route::rule("api/corpid","dingapi/api/index");
Route::rule("api/getuser","dingapi/api/getuser");