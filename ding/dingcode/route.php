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

//工作台
Route::rule("workbench","dingapp/Workbench/index"); //工作台


//兑换模块
Route::rule("exchange","dingapp/Exchange/index");   //兑换中心
Route::rule("exchange/info","dingapp/Exchange/info"); //商品兑换详情
Route::rule("exchange/record","dingapp/Exchange/record"); //兑换记录

//接口路由
Route::rule("api/corpid","dingapi/api/index");
Route::rule("api/getuser","dingapi/api/getuser");