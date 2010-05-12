<?php
class_exists('Object') || require('lib/Object.php');
class Profile extends Object{
	
	public function __construct($attributes = ull){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
		
	private $photo_url;
	public function getPhoto_url(){
		return $this->photo_url;
	}
	public function setPhoto_url($val){
		$this->photo_url = $val;
	}
	
	private $address;
	public function getAddress(){
		return $this->address;
	}
	public function setAddress($val){
		$this->address = $val;
	}

	private $city;
	public function getCity(){
		return $this->city;
	}
	public function setCity($val){
		$this->city = $val;
	}

	private $state;
	public function getState(){
		return $this->state;
	}
	public function setState($val){
		$this->state = $val;
	}
	
	private $zip;
	public function getZip(){
		return $this->zip;
	}
	public function setZip($val){
		$this->zip = $val;
	}
	
	private $country;
	public function getCountry(){
		return $this->country;
	}
	public function setCountry($val){
		$this->country = $val;
	}


}