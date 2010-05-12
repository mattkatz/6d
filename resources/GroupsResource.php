<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Tag') || require('models/Tag.php');

class GroupsResource extends AppResource{
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
	public function delete($groups = null, $ids = null, Tag $group = null){
		if($groups !== null){
			Tag::delete_many('group', $groups);
		}elseif($ids !== null && $group !== null){
			$this->groups = Tag::findTagsByTextAndParents($group->text, $ids);
			Tag::delete_many('group', $this->groups);
		}
		$all_contacts = new Tag(array('id'=>-1, 'type'=>'group', 'text'=>'All Contacts'));
		$this->groups = Tag::findAllTagsForGroups();
		if($this->groups === null){
			$this->groups = array();
		}
		$this->groups = array_merge(array($all_contacts), $this->groups);
		$view = 'group/index';
		$this->output = $this->renderView($view);
		return $this->renderView('layouts/default');
	}	
}