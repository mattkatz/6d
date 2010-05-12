<?php
class_exists('AppResource') || require('AppResource.php');
class InfoResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $posts;
	public function get_info(){		
		$this->title = "PHP info";
		$this->output = phpinfo();
		return $this->renderView('layouts/default', null);
	}
	
}

?>