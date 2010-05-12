<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Random') || require('lib/Random.php');
class_exists('Person') || require('models/Person.php');
class_exists('FriendRequest') || require('models/FriendRequest.php');
class_exists('NotificationResource') || require('NotificationResource.php');
class FollowerResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}

	public function __destruct(){
		parent::__destruct();
	}
	public $person;
	public function get(){
		$this->person = null;
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		
		if(count($this->url_parts) > 1){
			$id = String::replace('/\..*$/', '', $this->url_parts[1]);
			$this->person = FriendRequest::findById($id);
			$this->title =  $this->person->name;
		}
		$this->output = $this->renderView('follower/show', array('errors'=>$errors));
		return $this->renderView('layouts/default', null);
		
	}
	public function put(FriendRequest $request){		
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}else{
			$request = FriendRequest::findById($request->id);
			$person = Person::findByUrl($request->url);
			if($request !== null){
				if($person === null){
					$person = new Person(array('email'=>$request->getEmail()
						, 'name'=>$request->getName()
						, 'url'=>$request->getUrl()
						, 'uid'=>uniqid()
						, 'session_id'=>session_id()
						, 'public_key'=>Random::getPassword()
						, 'is_approved'=>true
						, 'is_owner'=>false));
				}else{
					$person->public_key = Random::getPassword();
					$person->is_approved = true;
					$person->is_owner = false;
				}
				
				$person = Person::save($person);
				FriendRequest::delete($request);
				$this->sendNotification($person);
			}else{
				if($person !== null){
					$this->sendNotification($person);
				}
			}
		}
		$this->output = $this->renderView('follower/index');
		return $this->renderView('layouts/default');
	}
	private function sendNotification($person){
		$config = new AppConfiguration();
		$site_path = String::replace('/http(s)?\:\/\//', '', FrontController::$site_path);
		$site_path = String::replace('/\/$/', '', $site_path);
		$data = sprintf("_method=put&email=%s&url=%s&public_key=%s", urlencode($config->email), urlencode($site_path), urlencode($person->public_key));
		$response = NotificationResource::sendNotification($person, 'followers', $data, 'post');
		UserResource::setUserMessage(sprintf("%s has been made a friend.%s", $request->getName(), $response));
	}
	// Some has sent a friend request.
	public function post(Person $person){		
		$this->person = Person::findByUrl($person->url);
		if($this->person === null){
			$friend_request = FriendRequest::findByUrl($person->url);
			if($friend_request === null){
				$friend_request = new FriendRequest(array('name'=>$person->name, 'email'=>$person->email, 'public_key'=>$person->public_key, 'created'=>date('c'), 'url'=>$person->url));
				$friend_request = FriendRequest::save($friend_request);
				if($errors !== null && count($errors) > 0){
					$message = array();
					foreach($errors as $key=>$error){
						$message[] = sprintf("%s=%s", $key, $error);
					}
					return implode('&', $message);
				}
			}
		}else{
			// Someone has sent another friend request, but is already a friend.
			$friend_request = new FriendRequest(array('name'=>$this->person->name, 'email'=>$this->person->email, 'public_key'=>$this->person->public_key, 'created'=>date('c'), 'url'=>$this->person->url));
			$this->put($friend_request);
		}
		return 'ok';
	}
	public function delete(FriendRequest $request){
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		if($request->id > 0){
			$response = FriendRequest::delete($request);
			UserResource::setUserMessage('Request has been deleted: ' . $response);
		}
		$this->output = $this->renderView('follower/index');
		return $this->renderView('layouts/default');
	}
}