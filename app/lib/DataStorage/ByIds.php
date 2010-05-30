<?php
	class_exists('FindCommand') || require('FindCommand.php');
	class ByIds extends FindCommand{
		public function __construct($ids, $limit = 0, $orderBy = null){
			$this->ids = $ids;			
			parent::__construct(null, $limit, $orderBy);
		}
		public function __destruct(){
			parent::__destruct();
		}
		public $ids;
	}
?>