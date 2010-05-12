<?php
	class_exists('Object') || require('lib/Object.php');
	abstract class FindCommand extends Object{
		public function __construct($relationships, $limit, $order_by){
			$this->relationships = $relationships;
			$this->limit = $limit;
			$this->order_by = $order_by;
			parent::__construct(null);
		}
		public function __destruct(){}

		public $relationships;
		public $order_by;
		public $limit;
	}

?>