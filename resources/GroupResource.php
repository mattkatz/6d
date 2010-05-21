<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Tag') || require('models/Tag.php');
class_exists('Person') || require('models/Person.php');

class GroupResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
		if(! AuthController::isAuthorized()){
			FrontController::setRequestedUrl('addressbook');
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $groups;
	public $group;
	public $people;
	public function get($group_id){
		$this->title = 'Address Book';
		$this->people = Person::findAll();
		if($this->people == null){
			$this->people = array();
		}
		$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups();
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		$view = 'addressbook/index';
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}

	public function post(Tag $group = null){
		$view = 'group/index';
		$errors = array();
		if($group != null && $group->text != null){
			$group->type = 'group';
			if($group->parent_id > 0){
				$existing_tags = Tag::findTagsByTextAndParent_id($group->text, $group->parent_id);
			}else{
				$existing_tags = Tag::findGroupTagsByText($group->text);
			}
			$message = null;
			$errors = array();		
			if($existing_tags === null){
				list($this->group, $errors) = Tag::save($group);				
			}
		}
		
		if(count($errors) > 0){
			$message = $this->renderView('error/index', array('message'=>"The following errors occurred when saving groups. Please resolve and try again.", 'errors'=>$errors));
			self::setUserMessage($message);				
		}
		/*$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups();
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		*/
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}
	
	
	public function delete(Tag $group = null){
		if($group != null && strlen($group->text) > 0){
			Tag::delete($group);
		}
		$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups();
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		$view = 'addressbook/index';
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}
	
}