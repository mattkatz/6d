<?php
	class_exists('FindCommand') || require('FindCommand.php');
	class All extends FindCommand{
		public function __construct($sql, $relationships, $limit = 0, $orderBy = null){
			$this->sql = $sql;
			parent::__construct($relationships, $limit, $orderBy);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $sql;
	}
?>