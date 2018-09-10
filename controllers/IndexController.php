<?php
namespace controllers;

class IndexController
{
    public function index()
    {
        $blog = new \models\Blog;
        $blogs = $blog->getNew();
        view('index.index',[
            'blogs' => $blogs
        ]);
    }
}