<?php
	class_exists('Object') || require('lib/Object.php');
	class Permission extends Object{
		public function __construct($args = null){
			parent::__construct($args);
			
			if(!isset($this->canSeeShout)){
				$this->canSeeShout = false;
			}
			
			if(!isset($this->isActive)){
				$this->isActive = false;
			}
			
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $canSeeShout;
		public $isActive;
		
		
		public static function unserialize($permission){
			return unserialize(str_replace('&semi;', ';', $permission));
		}
		
		public static function serialize($obj){
			return serialize($obj);
		}
	}
?>