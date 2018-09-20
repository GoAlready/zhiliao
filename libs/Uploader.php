<?php
    namespace libs;

    // 单例:三私一公
    class Uploader
    {
        private function __construct(){}
        private function __clone(){}
        // 保存唯一的对象(静态属性才是唯一的)
        private static $_obj = null;

        // 公开的方法获取对象
        public static function make()
        {
            if(self::$_obj === null)
            {
                // 生成一个对象
                self::$_obj = new self;
            }
            return self::$_obj;
            
        }

        private $_root = ROOT.'public/uploads/';    // 图片保存的一级目录
        private $_ext = ['image/jpeg','image/ejpeg','image/png','image/gif','image/bmp'];   //  允许上传的图片类型
        private $_maxSize = 1024 * 1024 * 1.8;  //  最大允许上传的尺寸 1.8MB
        private $_file;     //  保存用户上传的图片信息
        private $_subDir;

        // 上传图片
        // 参数一:表单中文件名
        // 参数二:保存到二级目录
        public function upload($name,$subdir)
        {
            // 把用户上传的图片信息保存到属性上
            $this->_file = $_FILES[$name];
            $this->_subDir = $subdir;

            if(!$this->_checkType())
            {
                die('图片类型不正确!');
            }

            if(!$this->_checkSize())
            {
                die('图片尺寸不正确!');
            }
            // 创建目录
            $dir = $this->_makeDir();
            // 生成唯一的名字
            $name = $this->_makeName();
            // 移动图片
            move_uploaded_file($this->_file['tmp_name'],$this->_root.$dir.$name);
            // 返回上传之后的图片路径
            return $dir.$name;
        }

        // 创建目录
        public function _makeDir()
        {
            
            // 获取当前日期
            $dir = $this->_subDir.'/'.date('Ymd');
            
            if(!is_dir($this->_root.$dir))
            
                mkdir($this->_root.$dir,0777,true); //  创建所有目录

            return $dir.'/';
            
        }

        // 生成唯一的名字
        private function _makeName()
        {
            // 获取文件扩展名
            $ext = strrchr($this->_file['name'],'.') ;  //如: .jpg
            //生成唯一的文件名
            $name = md5( time() . rand(1,9999) );
            // 完整文件名
            return $name . $ext;
        }

        private function _checkType()
        {
            return in_array($this->_file['type'],$this->_ext);
        }

        private function _checkSize()
        {
            return $this->_file['size'] < $this->_maxSize;
        }
    }
?>