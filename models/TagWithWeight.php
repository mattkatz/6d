<?php
	class TagWithWeight extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
		public function __destruct(){
			parent::__destruct();
		}
		private $text;
		public function getText(){
			return $this->text;
		}
		public function setText($val){
			$this->text = $val;
		}

		private $type;
		public function getType(){
			return $this->type;
		}
		public function setType($val){
			$this->type = $val;
		}

		private $weight;
		public function getWeight(){
			return $this->weight;
		}
		public function setWeight($val){
			$this->weight = $val;
		}
		
		
	}
?>