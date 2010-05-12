<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Post') || require('models/Post.php');
class_exists('Person') || require('models/Person.php');
class_exists('Profile') || require('models/Profile.php');
class_exists('LoginResource') || require('LoginResource.php');
class ProfileResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $person;
	public function get($state = null, Person $person = null){
		if(count($this->url_parts) > 1){
			// Get the person's photo.
			$photo_file_type = '.png';
			$matches = String::find('/\.(.+$)/', $this->url_parts[1]);
			if(count($matches) > 0){
				$photo_file_type = $matches[1];
			}
			// TODO: Need to secure this.
			$owner = Person::findOwner();
			if($owner->profile === null || strlen($owner->profile) === 0){
				$owner->profile = new Profile(null);
			}else{
				$owner->profile = unserialize($owner->profile);
			}
			if(!Application::isPhotoPublic()){
				$person = Person::findByPublicKey(urldecode($person->public_key));
				if($person !== null && $person->is_approved){
					$this->person = $owner;
					$this->output = $this->renderView('profile/photo');
					return $this->renderView('layouts/default');
				}else{
					throw new Exception(FrontController::NOTFOUND, 404);
				}
			}else{
				$this->person = $owner;
				$this->output = $this->renderView('profile/photo');
				return $this->renderView('layouts/default');
			}
		}else{
			$this->person = Person::findByEmail($this->config->email);
			if($this->person === null){
				$this->person = new Person();
			}
			if($this->person->profile !== null){
				$this->person->profile = unserialize($this->person->profile);
			}
			$this->title = $this->person->name . "'s profile.";
			if($state === 'modify'){
				if(! AuthController::isAuthorized()){
					throw new Exception(FrontController::UNAUTHORIZED, 401);
				}
				$this->output = $this->renderView('profile/edit', null);
				return $this->renderView('layouts/default', null);
			}else{
				if($this->person->profile === null || strlen($this->person->profile) === 0){
					$this->person->profile = serialize(new Profile(null));
				}
				$this->output = $this->renderView('profile/index', null);
				return $this->renderView('layouts/default', null);
			}
		}
	}
	public static function getPhotoUrl(Person $person, $photo_file_type = '.png'){
		if($person->profile === null || $person->profile->photo_url === null){
			return FrontController::urlFor('images') . 'mant-emo/grin.png';
		}else if(strpos($person->profile->photo_url, 'http') !== false){
			return $person->profile->photo_url;
		}else if($person->profile->photo_url !== null){
			return str_replace('index.php', '', FrontController::urlFor(null)) . $person->profile->photo_url;
		}
	}
	public function put(Person $person){
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$person->setSession_id(session_id());
		$owner = Person::findOwner();
		if($owner !== null){
			$owner->setIs_owner(false);
			Person::save($owner);
		}
		$person->setIs_owner(true);
		if($person->id > 0){
			$existing_person = Person::findById($person->id);
			$person->setUid($existing_person->getUid());
		}else{
			$person->setUid(uniqid());
		}
		if($person->profile != null){
			$profile = new Profile(array('photo_url'=>$person->profile['photo_url'], 'address'=>$person->profile['address']
				, 'city'=>$person->profile['city'], 'state'=>$person->profile['state'], 'zip'=>$person->profile['zip'], 'country'=>$person->profile['country']));
			$person->setProfile(serialize($profile));
		}
		$person->url = String::replace('/http[s]?\:\/\//', '', FrontController::$site_path);
		$person->url = String::replace('/\/$/', '', $person->url);
		$person->is_approved = true;
		$person = Person::save($person);
		if(count($errors) == 0){
			self::setUserMessage('Profile saved');
			$this->redirectTo('profile');
		}else{
			$message = $this->renderView('install/error', array('message'=>"The following errors occurred when saving your profile. Please resolve and try again.", 'errors'=>$errors));					
			self::setUserMessage($message);
			$this->redirectTo('profile');
		}
	}
}

?>