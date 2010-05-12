<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Person') || require('models/Person.php');
class PostsResource extends AppResource{
	public function __construct($attributes){
		parent::__construct($attributes);
	}
	public function __destruct(){}
	public $posts;
	public $page;
	public $sort_by;
	public $sort_by_direction;
	public $limit;
	
	public function get($id = null){
		if(!AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		$this->limit = 5;
		array_shift($this->url_parts);
		if(count($this->url_parts) > 0){
			$this->page = (int)array_shift($this->url_parts);
		}
		if($this->page <= 0){
			$this->page = 1;
		}
		$start = ($this->page-1) * $this->limit;

		$this->sort_by = 'post_date';
		$this->sort_by_direction = 'desc';
		$tag = null;
		if(count($this->url_parts) > 0){
			$tag = array_shift($this->url_parts);
			switch($tag){
				case('author'):
					$author_id = $this->url_parts[2];
					$person = new Person(array('id'=>$author_id));
					if($person->id > 0){
						$person = Person::findById($person->id);
						if($person !== null){
							if($person->is_owner){
								$person->url = null;
							}
							$this->posts = Post::findByPerson($person, $start, $this->limit, $this->sort_by, $this->sort_by_direction);
						}
					}
					break;
				default:
					if(count($this->url_parts) > 0){
						$this->page = array_shift($this->url_parts);
					}else{
						$this->page = 1;
					}
					$this->initializePosts($tag, $start, $this->limit, $this->sort_by, $this->sort_by_direction);
					break;
			}
		}else{
			if($tag !== null){
				$this->posts = Post::findByTag($tag, $start, $this->limit, $this->sort_by, $this->sort_by_direction);
			}else{
				$this->posts = Post::find($start, $this->limit, $this->sort_by, $this->sort_by_direction);			
			}
		}
		$this->output = $this->renderView('post/index');
		return $this->renderView('layouts/default');
	}
	private function initializePosts($tag = null, $start = 0, $limit = 4, $sort_by = 'post_date', $sort_by_direction = 'desc'){
	}
}