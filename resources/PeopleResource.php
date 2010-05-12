<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Person') || require('models/Person.php');
class_exists('FriendRequest') || require('models/FriendRequest.php');
class PeopleResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}

	public function __destruct(){
		parent::__destruct();
	}

	public $people;
	public $person;
	public $follow_requestors;
	
	public function get(Tag $group = null){
		if(count($this->url_parts) > 1){
			$group = new Tag(array('text'=>urldecode(String::replace('/\..*$/', '', $this->url_parts[1])), 'type'=>'group'));
		}
		if(! AuthController::isAuthorized()){
			FrontController::setRequestedUrl('people');
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$this->person = new Person();
		if($group->text !== 'All Contacts'){
			$this->people = Person::findByTagText($group->text);
		}elseif($this->group->text === 'Friend Requests'){
			$this->people = FriendRequest::findAll();
		}else{
			$this->people = Person::findAll();
		}
		if($this->people == null){
			$this->people = array();
		}else{
			usort($this->people, array('Person', 'sort_by_name'));
		}
		$this->title = 'People';
		$this->output = $this->renderView('person/index', null);
		return $this->renderView('layouts/default', null);
	}
	public function delete($ids = array()){
		if($ids !== null && strlen($ids) > 0){
			$ids = explode(',', $ids);
			$deleted_people = Person::delete_many($ids);
		}
		$this->people = Person::findAll();
		$this->title = 'People';
		$this->output = $this->renderView('person/index', null);
		return $this->renderView('layouts/default', null);
	}
}