<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('NotificationResource') || require('resources/NotificationResource.php');
class_exists('Person') || require('models/Person.php');
class_exists('FriendRequest') || require('models/FriendRequest.php');
class FollowersResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}

	public function __destruct(){
		parent::__destruct();
	}
	public $person;
	public $people;
	public function get(){
		$errors = array();
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}else{
			$this->people = FriendRequest::findAll();
			$this->title = 'Friend Requests';
			$this->output = $this->renderView('follower/index', array('errors'=>$errors));
			return $this->renderView('layouts/default', null);
		}
		
	}
	// If someone confirms the friend request, a request is made to this method.
	public function put(Person $person){
		//TODO: check remote host against the url to verify who's sending the response.
		//error_log(sprintf('request from: host=%s, referrer=%s, ip=%s, public key = %s', $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'], $_SERVER['REMOTE_ADDR'], urlencode($person->public_key)));
		if($person->public_key !== null && strlen($person->public_key) > 0 && $person->url !== null && strlen($person->url) > 0){
			$this->person = Person::findByUrl($person->url);
			$this->person->setPublic_key($person->public_key);
			$this->person = Person::save($this->person);
		}
		return 'ok';
		
	}
	public function post(Person $person){
		$errors = array();
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}elseif($person->id !== null){
			$this->person = Person::findById($person->id);
			if($this->person->url !== null && strlen($this->person->url) > 0){
				$config = new AppConfiguration();
				$owner = Person::findOwner();
				$site_path = String::replace('/\/$/', '', FrontController::$site_path);
				$data = sprintf("email=%s&name=%s&url=%s&created=%s", urlencode($owner->email), urlencode($owner->name),  urlencode(str_replace('http://', '', $site_path)), urlencode(date('c')));
				$response = NotificationResource::sendNotification($this->person, 'follower', $data, 'post');
				UserResource::setUserMessage($this->person->name . "'s site responded with " . $response);
				$this->title = 'Request Sent!';
				$this->output = $this->renderView('follower/confirmation');
			}else{
				$this->output = $this->renderView('follower/show', array('errors'=>$errors));
				$errors['url'] = "I need the person's website address to follow them.";
				UserResource::setUserMessage($errors['url']);
			}
			return $this->renderView('layouts/default', null);
		}
	}
}