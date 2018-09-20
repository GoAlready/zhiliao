<?php
namespace controllers;

class IndexController
{
    public function index()
    {
        // 取出最新日志
        $blog = new \models\Blog;
        $blogs = $blog->getNew();

        // 取出活跃用户
        $user = new \models\User;
        $users = $user->getActiveUsers();

        view('index.index',[
            'blogs' => $blogs,
            'users' => $users,
        ]);
    }
}