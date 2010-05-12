<?php
	class_exists('Notification') || require('models/Notification.php');
	class_exists('AppResource') || require('AppResource.php');
	class_exists('UserResource') || require('UserResource.php');
	class_exists('String') || require('lib/String.php');
	class_exists('Post') || require('models/Post.php');
	class_exists('aes128') || require('lib/aes128lib/aes128.php');
	class_exists('Request') || require('lib/Request.php');
	class NotificationResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
		}
	
		public function __destruct(){
			parent::__destruct();
		}
		public $notifications;
		public $posts;
		public function get(){
			$this->notifications = Notification::findRecent();
			$this->posts = array();
			foreach($this->notifications as $notification){
				$this->posts[] = json_decode($this->getFromFriend($notification));
			}
			$this->title = 'Notifications';
			$this->output = $this->renderView('notification/recent', null);
			return $this->renderView('layouts/default', null);
		}
		private function getFromFriend(Notification $notification){
			$type = $notification->name;
			$url = sprintf("http://%s/index.php?r=%s.json", $notification->appName, strtolower(String::pluralize($notification->name)));
			$response = Request::doRequest($url, null, 'get');
			return $response;
		}
		public function post(Notification $notification){
			$notification->appName = urldecode($notification->appName);
			$notification->time = urldecode($notification->time);
			error_log($notification->name . ' ' . $notification->time);
			$errors = Notification::save($notification);
			if($errors != null && count($errors) > 0){
				error_log('notification failed to save');
				return 'not ok';
			}else{
				return 'ok';
			}
		}
		
		public static function sendMultiNotifications($people, $resourceName, $datum, $type = 'get'){
			$urls = array();
			$responses = array();
			$config = new AppConfiguration();
			$segments = explode('/', $_SERVER['SCRIPT_NAME']);
			array_pop($segments);
			$path = implode('/', $segments);
			$appName = sprintf('%s%s', $_SERVER['SERVER_NAME'], $path);
			foreach($people as $person){
				$person->url = preg_replace('/\/$/', '', $person->url);
				$person->url = sprintf("http://%s", $person->url);
				$urls[] = $person->url;
			}
			$path = sprintf("index.php/%s", $resourceName);
			$responses = Request::doMultiRequests($urls, $path, $datum, $type, null);	
			return $responses;
		}
		public static function sendNotification($person, $name, $data, $type = 'get'){
			$config = new AppConfiguration();
			$segments = explode('/', $_SERVER['SCRIPT_NAME']);
			array_pop($segments);
			$path = implode('/', $segments);
			$person->url = preg_replace('/\/$/', '', $person->url);
			$appName = sprintf('%s%s', $_SERVER['SERVER_NAME'], $path);
			$url = sprintf("http://%s", $person->url);
			$path = sprintf("index.php/%s", $name);
			/*$aes = new aes128();
			$key = $aes->makeKey($follower->public_key);
			error_log($follower->public_key);
			error_log(serialize($obj));
			$body = $aes->blockEncrypt(serialize($obj),$key);
			error_log($body);
			*/
			$response = Request::doRequest($url, $path, $data, $type, null);			
			return $response;
		}		
	}
?>