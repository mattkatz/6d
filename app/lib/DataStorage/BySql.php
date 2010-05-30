<?php
	class_exists('FindCommand') || require('FindCommand.php');
	class BySql extends FindCommand{
		public function __construct($sql, $relationships = null, $limit = 0){
			$this->sql = $sql;
			parent::__construct($relationships, $limit, null);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $sql;
	}
?>