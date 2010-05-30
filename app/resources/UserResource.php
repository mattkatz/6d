<?php
class_exists('Resource') || require('lib/Resource.php');
    class UserResource extends Resource{
        public function __construct($attributes = null){
            parent::__construct($attributes);
        }
        
        public function __destruct(){
            parent::__destruct();
        }

		public static function avatar($size){
			$config = new AppConfiguration();
			$url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5( strtolower($config->email) )."&size=".$size;
			return $url;
		}
		
    }
?>