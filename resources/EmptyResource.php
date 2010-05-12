<?php
class_exists('AppResource') || require('AppResource.php');
class EmptyResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function get(){		
		return '';
	}
}

?>