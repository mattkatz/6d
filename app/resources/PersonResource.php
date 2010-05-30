<?php
class_exists('Random') || require('lib/Random.php');
class_exists('AppResource') || require('AppResource.php');
class_exists('LoginResource') || require('LoginResource.php');
class_exists('Person') || require('models/Person.php');
class_exists('aes128') || require('lib/aes128lib/aes128.php');
class_exists('NotificationResource') || require('NotificationResource.php');
	class PersonResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
	
		public function __destruct(){
			parent::__destruct();
		}

		public $people;
		public $person;
		public function get(Person $person = null){
			if(! AuthController::isAuthorized()){
				FrontController::setRequestedUrl('person');
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			if(count($this->url_parts) > 1){
				$person = new Person(array('id'=>$this->url_parts[1]));
			}
			if($person != null && $person->id > 0){
				$this->person = Person::findById($person->id);
				$this->title = 'Person: ' . $this->person->email;				
				$this->output = $this->renderView('person/show', null);				
				return $this->renderView('layouts/default', null);
			}else{
				$this->person = new Person();
				$this->title = "Add a person";
				$this->output = $this->renderView('person/show', null);
				return $this->renderView('layouts/default', null);
			}
			
		}
		public function delete(Person $person){
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			if($person->id == null){
				throw new Exception('Person id must be set');
			}
			$person = Person::findById($person->id);
			if(!$person->is_owner){
				Person::delete($person);
			}else{
				UserResource::setUserMessage("You can't delete the owner of the site.");
			}
			$this->redirectTo('addressbook');
		}
		
		public function put(Person $person, Profile $profile = null){
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}else{
				$view = 'person/show';
				$this->person = Person::findById($person->id);
				if($this->person !== null){
					$this->person->is_approved = $person->is_approved;
					$this->person->email = $person->email;
					$this->person->url = $person->url;
					$this->person->name = $person->name;
					$this->person->session_id = session_id();
					if($profile !== null){
						$this->person->profile = serialize($profile);
					}
					$this->person = Person::save($this->person);
					if($errors != null && count($errors) > 0){
						$message = array();
						foreach($errors as $key=>$value){
							$message[] = sprintf("%s: %s", $key, $value);
						}
						UserResource::setUserMessage('Failed to save person - ' . implode(', ', $message));
					}else{
						UserResource::setUserMessage("{$this->person->name}'s info has been saved.");
					}
				}
				$this->output = $this->renderView($view, array('errors'=>$errors));
				return $this->renderView('layouts/default');					
				
			}
		}
		public function post(Person $person, Profile $profile = null){
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}else{
				$view = 'person/index';
				$this->person = $person;
				// Posting to a resource means you're creating a new object of this type.
				// I added this logic to assert that assumption.
				if($person->id === null || strlen($person->id) === 0){
					$this->person->setSession_id(session_id());
					$this->person->setUid(uniqid());
					$this->person->setIs_approved(true);
					$this->person->setIs_owner(false);
					if($profile !== null){
						$this->person->profile = serialize($profile);
					}
					$this->person = Person::save($this->person);
					if($errors != null && count($errors) > 0){
						$message = array();
						foreach($errors as $key=>$value){
							$message[] = sprintf("%s: %s", $key, $value);
						}
						UserResource::setUserMessage('Failed to save person - ' . implode(', ', $message));
					}else{
						$this->people = Person::findAll();
					}
				}				
				$this->output = $this->renderView($view, array('errors'=>$errors));
				return $this->renderView('layouts/default');					
			}
		}
	}
?>