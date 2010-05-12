<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Person') || require('models/Person.php');
class LoginResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function get(){
		$this->title = 'Login';
		$this->output = $this->renderView('user/login');
		return $this->renderView('layouts/default');			
	}
	
	public function post($email, $password = null){
		$isAuthed = false;
		if( AuthController::isAuthorized()){
			$isAuthed = true;
		}
		if(empty($email) || empty($password)){
			$isAuthed = false;
		}else{
			$isAuthed = self::doVerification($email, $password);			
		}
		if($isAuthed){
			if($email != null && !empty($email)){
				AuthController::setAuthKey($email, $password);					
			}
			$this->redirectTo(FrontController::requestedUrl());
		}else{
			self::setUserMessage($this->renderView('error/login', array('errors'=>array('auth'=>'authorization failed'), 'message'=>"Those credentials can't be found. If you're really trying to sign in, please try it again.")));
			$this->redirectTo('login');
		}
	}
		
	public static function doVerification($email, $password){
		// I'm going to see if this is the admin trying to log, if not check the db to see verify a user.
		$config = new AppConfiguration(null);
		$password = $password;
		$email = $email;
		if($config->email === $email && $config->site_password === $password){
			return true;
		}else{
			$person = Person::findByEmailAndPassword($email, $password);
			if($person != null){
				UserResource::setPersonId($person->id);
				return true;
			}else{
				return false;
			}
		}
	}
	
}
?>