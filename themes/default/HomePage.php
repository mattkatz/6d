<?php

class HomePage{
	
	public function __construct(){
		$this->limit = 4;
	}
	public function __destruct(){}
	private $limit;
	public function getLimit(){
		return $this->limit;
	}

}