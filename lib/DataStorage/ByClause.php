<?php
	class_exists('FindCommand') || require('FindCommand.php');
	class ByClause extends FindCommand{
		public function __construct($clause, $relationships = null, $limit = 0, $orderBy = null){
			$this->clause = $clause;
			parent::__construct($relationships, $limit, $orderBy);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $clause;
	}
?>