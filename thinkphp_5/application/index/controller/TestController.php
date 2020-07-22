<?php
namespace app\index\controller;
use think\Db;

class TestController
{
    public function index()
    {
      $posts = input("post.password");
      return json($posts);

    }

    public function verify()
   {
       $posts = input("post.password");
       return json($posts);
   }
}
