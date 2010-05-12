<?php
class_exists('Random') || require('lib/Random.php');
class_exists('AppResource') || require('AppResource.php');
class_exists('LoginResource') || require('LoginResource.php');
class_exists('WannabeFriend') || require('models/WannabeFriend.php');
class_exists('Friend') || require('models/Friend.php');
class_exists('aes128') || require('lib/aes128lib/aes128.php');
class_exists('NotificationResource') || require('NotificationResource.php');
	class WannabefriendResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
	
		public function __destruct(){
			parent::__destruct();
		}

		public $friends;
		public $friend;
		public function get(WannabeFriend $friend = null){
			if(! AuthController::isAuthorized()){
				FrontController::setRequestedUrl('wannabefriend');
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			if($friend != null && $friend->id > 0){
				$this->friend = WannabeFriend::findById($friend->id);
				$this->title = 'WannabeFriend: ' . $this->friend->name;
				$this->output = $this->renderView('wannabefriend/show', null);
				return $this->renderView('layouts/default', null);
			}else{
				$this->friend = new WannabeFriend();
				$this->title = "Add a friend";
				$this->output = $this->renderView('wannabefriend/show', null);
				return $this->renderView('layouts/default', null);
			}
			
		}

		public function delete(WannabeFriend $friend){
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			if($friend->id == null){
				throw new Exception('WannabeFriend id must be set');
			}
			WannabeFriend::delete($friend);
			$this->redirectTo('friends');
		}
		
		public function post_wannabefriend_follow(WannabeFriend $friend){
			NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', FrontController::$site_path . ($friend == null ? 'friend is null' : 'not null '), $this);
			NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'Saving wannabe friend', $this);
			$errors = array();
			$this->friend = Friend::findByUrl($friend->url);
			if($this->friend == null){
				error_log($friend->name . ' ' . $friend->url);
				$this->friend = WannabeFriend::findByUrl($friend->url);
				if($this->friend === null){
					$this->friend = $friend;
					$this->friend->is_approved = false;
					$this->friend->public_key = null;
					$this->friend->time = date('c');
					$errors = WannabeFriend::save($this->friend);					
					if($errors != null && count($errors) > 0){
						$message = array();
						foreach($errors as $key=>$error){
							$message[] = sprintf("%s=%s", $key, $error);
						}
						NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', implode('&', $message), $this);
					}
				}
			}
			$this->title = 'Follow me?';
			$this->output = $this->renderView('wannabefriend/index', null);
			return $this->renderView('layouts/default', null);
		}
		
		private function respond_to_follow_request(Friend $friend){
			$config = new AppConfiguration();
			$site_path = trim(FrontController::urlFor(null), '/');
			$data = sprintf("email=%s&url=%s&time=%s&public_key=%s", urlencode($config->email), urlencode(str_replace('http://', '', $site_path)), urlencode(date('c')), $friend->public_key);
			return NotificationResource::sendNotification($friend, 'friend/approval', $data, 'post');
		}
		
		public function post(WannabeFriend $wannabefriend){
			$errors = array();
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}else{
				WannabeFriend::save($wannabefriend);
				if(!$wannabefriend->is_ignored){
					$wannabefriend = WannabeFriend::findById($wannabefriend->id);
					$this->friend = new Friend(array('name'=>$wannabefriend->name, 'email'=>$wannabefriend->email, 'url'=>$wannabefriend->url, 'public_key'=>Random::getPassword(), 'is_approved'=>true, 'time'=>date('c')));
					$response = $this->respond_to_follow_request($this->friend);
					if($response == 'ok'){
						list($this->friend, $errors) = Friend::save($this->friend);					
					}else{
						$errors['response'] = $response;
					}
					if($errors != null && count($errors) > 0){
						self::setUserMessage($this->renderView('error/index', array('message'=>'Failed to save', 'errors'=>$errors)));
					}else{
						WannabeFriend::delete($wannabefriend);
					}
				}
			}
			$this->title = 'Follow Request';
			$this->output = $this->renderView('wannabefriend/show', array('errors'=>$errors));
			return $this->renderView('layouts/default', null);
			
		}
	}
?>