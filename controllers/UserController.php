<?php
    namespace controllers;

    use models\User;
    
    class UserController {
        public function hello(){
            // 取模型里的数据
            $user = new User;
            $name = $user->getName();
            
            // 加载视图
            view('users.hello',[
                'name' => $name
                ]);

        }
    }
?>