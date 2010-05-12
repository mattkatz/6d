<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('WannabeFriend') || require('models/WannabeFriend.php');
class_exists('Friend') || require('models/Friend.php');

class WannabefriendsResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}

	public function __destruct(){
		parent::__destruct();
	}

	public $friends;
	public function get(){
		if(! AuthController::isAuthorized()){
			FrontController::setRequestedUrl('friends');
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$this->friend = new WannabeFriend();
		$this->friends = WannabeFriend::findAll();
		if($this->friends == null){
			$this->friends = array();
		}
		$this->title = 'Wannabe your friends';
		$this->output = $this->renderView('wannabefriend/index', null);
		return $this->renderView('layouts/default', null);
		
	}
}