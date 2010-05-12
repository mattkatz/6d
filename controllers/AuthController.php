<?php

class AuthController{
	public function __construct(){}
	public function __destruct(){}
	public static function isAuthorized(){
		$sessionAuthKey = self::authKey();
		return $sessionAuthKey !== null;
	}
	public static function authKey(){
		$sessionAuthKey = (array_key_exists('authKey', $_SESSION) && !empty($_SESSION['authKey']) ? $_SESSION['authKey'] : null);			
		return $sessionAuthKey;
	}
	public static function setAuthKey($email, $password){
		$_SESSION['authKey'] = $password;
	}
	public static function logout(){
		session_unset($_SESSION['authKey']);
		session_destroy();
	}
	
}