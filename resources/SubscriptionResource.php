<?php
class_exists('Random') || require('lib/Random.php');
class_exists('AppResource') || require('AppResource.php');
class_exists('FollowerResource') || require('FollowerResource.php');
	class SubscriptionResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
	
		public function __destruct(){
			parent::__destruct();
		}

		public $follower;		
		public function post_subscription(Follower $follower){
			$this->follower = Follower::findById($follower->id);
			if($this->follower == null){
				$this->follower = new Follower();
			}
			$this->follower->is_approved = false;
			$this->follower->email = $follower->email;
			$this->follower->url = urldecode($follower->url);
			$this->follower->time = date(time());
			if($this->follower->id == null || $this->follower->id == 0 || $this->follower->private_key == null){
				$this->follower->private_key = Random::getPassword();
				$this->follower->public_key = Random::getPassword();				
			}
			$errors = Follower::save($this->follower);
			if($errors != null && count($errors) > 0){
				error_log('follower failed to save');
				UserResource::setUserMessage("Follower failed to save.");
			}
			return $this->renderView('subscription/success', null);
		}
	}
?>