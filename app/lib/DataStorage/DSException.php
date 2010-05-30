<?php
	class DSException extends Exception{
		public function __construct($e){
			parent::__construct($e->getMessage(), $e->getCode());
		}
		public function __destruct(){}
	}
?>