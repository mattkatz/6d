<?php
	class_exists('Object') || require('lib/Object.php');
	class Relationship extends Object{
		public function __construct($args){
			parent::__construct($args);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $through;
		public $withWhom;
		
	}
?>