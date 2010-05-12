<?php
class_exists('LoginResource') || require('LoginResource.php');
class_exists('AppResource') || require('AppResource.php');
class_exists('PhotoResource') || require('PhotoResource.php');
class_exists('ProfileResource') || require('ProfileResource.php');
class_exists('Post') || require('models/Post.php');
class_exists('Photo') || require('models/Photo.php');
class_exists('NotificationResource') || require('NotificationResource.php');
class_exists('Person') || require('models/Person.php');
class PostResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
		$this->max_filesize = str_replace('M', '', ini_get('upload_max_filesize')) * 1000000;
		$this->post = new Post();
	}
	public function __destruct(){
		parent::__destruct();
	}
	private $notificationResource;
	public $posts;
	public $post;
	public $max_filesize;
	public $page;
	public $photos;
	public $people;
	
	public function get(Post $post = null, $layout = 'default'){
		$photo = new Photo();
		$this->photos = $photo->findAll();
		$view = 'post/show';
		$layout = 'layouts/' . $layout;
		if( AuthController::isAuthorized()){
			$view = 'post/edit';
		}
		if(count($this->url_parts) > 1){
			$this->post = Post::findById($this->url_parts[1]);
		}else{
			$this->post = $post;
		}
		if($this->post != null && strlen($this->post->id) > 0){
			$this->title = $this->post->title;
			$this->description = $this->post->description;
			$this->output = $this->renderView($view, null);
			return $this->renderView($layout, null);
		}else{
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			$this->post = new Post();
			$this->title = "New post";
			$this->output = $this->renderView($view, null);
			return $this->renderView($layout, null);
		}
	}
	public static function getAuthorUrl(Post $post){
		$url = '';
		if($post->source !== null && strlen($post->source) > 0){
			$person = Person::findByUrl($post->source);
			if($person !== null){
				$data = sprintf("public_key=%s", urlencode($person->public_key));
				$response = NotificationResource::sendNotification($person, 'profile.json', $data, 'get');
				$response = json_decode($response);
				$url = $response->person->photo_url;
			}else{
				$url = 'images/weeble.jpg';
			}
		}else{
			$config = new AppConfiguration();
			$person = Person::findByEmail($config->email);
			$person->profile = unserialize($person->profile);
			$url = ProfileResource::getPhotoUrl($person);
		}
		return $url;		
	}
	public function put(Post $post, $people = array(), $groups = array(), $make_home_page = false, $public_key = null, $photo_names = array()){
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}else{
			if($post->id !== null && strlen($post->id) > 0){
				$this->post = Post::findById($post->id);
			}
			
			if($this->post !== null){
				list($post, $errors) = Post::save($post);
				if($errors == null){
					if($make_home_page){
						$setting = Setting::findByName('home_page_post_id');
						$setting->value = $post->id;
						Setting::save($setting);
					}else if($post->isHomePage($this->getHome_page_post_id())){
						Setting::delete('home_page_post_id');
					}
					self::setUserMessage('Post was saved.');
					$this->sendPostToPersons($groups, $people, $post);
				}else{
					$message = 'An error occurred while saving your post:';
					foreach($errors as $key=>$value){
						$message .= "$key=$value";
					}
					self::setUserMessage($message);
				}
			}else{
				self::setUserMessage("That post doesn't exist.");
			}
			$this->redirectTo('posts');
		}		
	}
	public function post(Post $post, $people = array(), $groups = array(), $make_home_page = false, $public_key = null, $photo_names = array()){
		$errors = array();
		if($public_key != null && strlen($public_key)>0){
			$person = Person::findByPublicKey($public_key);
			$response = 'ok';
			if($person != null && $person->is_approved){
				$existing_post = Post::findByPersonPostId($post->person_post_id);
				if($existing_post != null){
					$post->id = $existing_post->id;
					$post->is_published = $existing_post->is_published;
				}else{
					$post->is_published = false;
					$post->source = $person->url;
					$post->id = null;
				}
				$post->created = date('c');
				if($post->post_date === null || strlen($post->post_date) === 0 || $post->post_date == 'today'){
					$post->post_date = date('c');
				}else{
					
				}
				$post->body = $this->filterBody($post->body);
				$post->title = $this->filterBody($post->title);
				$post->body = urldecode($post->body);
				list($post, $errors) = Post::save($post);
				if(count($errors) > 0){
					foreach($errors as $key=>$error){
						error_log("an error occured: $key=$error");
					}
				}
			}else{
				$response = "Couldn't find a person with the given public key.";
			}
			return $response;
		}elseif(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}else{
			$post->created = date('c');
			if(strlen($post->post_date) === 0){
				$post->post_date = date('c');
			}
			list($post, $errors) = Post::save($post);
			if($errors == null){
				if($make_home_page){
					$setting = Setting::findByName('home_page_post_id');
					$setting->value = $post->id;
					Setting::save($setting);
				}else if($post->isHomePage($this->getHome_page_post_id())){
					Setting::delete('home_page_post_id');
				}
				self::setUserMessage('Post was saved.');
				$this->sendPostToPersons($groups, $people, $post);
			}else{
				$message = 'An error occurred while saving your post:';
				foreach($errors as $key=>$value){
					$message .= "$key=$value";
				}
				self::setUserMessage($message);
			}
			$this->redirectTo('posts');
		}		
	}
	public function delete(Post $post){
		if(! AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$post = Post::findById($post->id);
		Post::delete($post);
		self::setUserMessage(sprintf("'%s' was deleted.", $post->title));
		$this->redirectTo('posts');
	}
	private function sendPostToPersons($groups, $people, Post $post){
		$data = null;
		if(count($groups) > 0){
			foreach($groups as $text){
				if($text === 'All+Contacts'){
					$this->people = Person::findAll();
				}else{
					$this->people = Person::findByTagText($text);
				}
				$this->sendToPeople($this->people, $post);
			}
		}
		if(count($people) > 0){
			$this->people = Person::findByIds($people);
			if($this->people !== null && count($this->people) > 0){
				$this->sendToPeople($this->people, $post);
			}
		}		
	}
	private function sendToPeople($people, $post){
		$datum = array();
		$responses = array();
		$to = array();
		foreach($people as $person){
			if(!$person->is_owner > 0 && $person->is_approved){
				$datum[] = sprintf("person_post_id=%s&title=%s&body=%s&source=%s&is_published=%s&post_date=%s&public_key=%s", urlencode($post->id), urlencode($post->title), urlencode($post->body), urlencode($post->source), $post->is_published, urlencode($post->post_date), urlencode($person->public_key));
				$to[] = $person;
			}
		}
		$responses = NotificationResource::sendMultiNotifications($to, 'post', $datum, 'post');
		if(count($responses) > 0){
			$message = array();
			foreach($responses as $key=>$response){
				$person = $to[$key];
				UserResource::setUserMessage($person->name . ' responded with ' . $response);
			}
		}
	}
	private function sendToPerson($person, $post){
		if($person->is_approved){
			$data = sprintf("person_post_id=%s&title=%s&body=%s&source=%s&is_published=%s&post_date=%s&public_key=%s", urlencode($post->id), urlencode($post->title), urlencode($post->body), urlencode($post->source), $post->is_published, urlencode($post->post_date), urlencode($person->public_key));
			$response = NotificationResource::sendNotification($person, 'post', $data, 'post');
			if($response !== 'ok'){
				UserResource::setUserMessage($response);
			}
			$data = null;
		}
	}
}

?>
