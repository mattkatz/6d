<?php
class_exists('AppResource') || require('AppResource.php');
class LogoutResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function get(){
		AuthController::logout();
		self::setUserMessage("You've been logged out.");
		$this->redirectTo(null);
	}
	
}
?>