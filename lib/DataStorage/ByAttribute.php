<?php
	class_exists('FindCommand') || require('FindCommand.php');
	class ByAttribute extends FindCommand{
		public function __construct($name, $value, $limit = 0, $orderBy = null){
			$this->name = $name;
			$this->value = $value;
			parent::__construct(null, $limit, $orderBy);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $name;
		public $value;
	}
?>