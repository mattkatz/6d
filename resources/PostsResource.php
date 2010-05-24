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
		$tag = null;	
		$this->limit = 5;
		array_shift($this->url_parts);
		if(count($this->url_parts) > 0){
			$this->page = intval($this->url_parts[0]);
			if($this->page === 0){
				$tag = array_shift($this->url_parts);
			}
		}
		if($this->page <= 0){
			$this->page = 1;
		}
		$start = ($this->page-1) * $this->limit;

		$this->sort_by = 'id';
		$this->sort_by_direction = 'desc';
		if($tag === 'author'){
			$author_id = array_shift($this->url_parts);
			$this->posts = $this->getPostsByAuthor($author_id);
		}else if($tag !== null){
			$this->title = 'All Posts Tagged ' . $tag->text;
			$this->posts = $this->getPostsByTag($tag);
		}else{
			$this->title = 'All Posts';
			$this->posts = $this->getAllPosts($start, $this->limit, $this->sort_by, $this->sort_by_direction);
		}
		if(count($this->posts) === 0){
			self::setUserMessage('There are no more posts to show for that request.');
			$this->redirectTo('posts');
		}
		$this->output = $this->renderView('post/index');
		$this->keywords = implode(', ', String::getKeyWordsFromContent($this->output));
		if($this->post !== null){
			$this->description = $this->post->title;
		}else{
			foreach($this->posts as $post){
				$this->description .= $post->title . ',';
			}
		}
		
		return $this->renderView('layouts/default');
	}
	private function getAllPosts($start, $limit, $sort_by, $sort_by_direction){
		return Post::find($start, $this->limit, $this->sort_by, $this->sort_by_direction);			
	}
	private function getPostsByTag($tag){
		return Post::findByTag($tag, $start, $this->limit, $this->sort_by, $this->sort_by_direction);
	}
	private function getPostsByAuthor($author){
		$person = new Person(array('id'=>$author_id));
		if($person->id > 0){
			$person = Person::findById($person->id);
			if($person !== null){
				if($person->is_owner){
					$person->url = null;
				}
				$posts = Post::findByPerson($person, $start, $this->limit, $this->sort_by, $this->sort_by_direction);
				$this->title = "All Posts by " . $person->name;
			}
		}
		return $posts;
	}
}