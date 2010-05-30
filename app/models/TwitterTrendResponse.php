<?php
	class_exists('Object') || require('lib/Object.php');
	class TwitterTrendResponse extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		private $as_of;
		public function getAsOf(){
			return $this->as_of;
		}
		public function setAsOf($val){
			$this->as_of = $val;
		}
		
		private $trends;
		public function getTrends(){
			return $this->trends;
		}
		public function setTrends($val){
			$this->trends = $val;
		}
		
	}
	
?>