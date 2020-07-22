<?php
namespace app\index\controller;
use think\Controller;
use think\Request;			// 引用Request
use app\common\model\Login;  //发票模型

class LoginController extends Controller
{
    // 用户登录表单
    public function index()
    {
      // 显示登录表单
      $htmls = $this->fetch();
      return $htmls;
    }

    // 处理用户提交的登录数据
    public function login()
    {
        return 'login';
    }
}
