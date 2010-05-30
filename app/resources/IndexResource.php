<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('Post') || require('models/Post.php');
class_exists('LoginResource') || require('LoginResource.php');
class IndexResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public $posts;
	public $post;
	public $page;
	public $sort_by;
	public $sort_by_direction;
	public $limit;
	public function get($id = 1, $sort_by = 'post_date', $sort_by_direction = 'desc', $direction = null, $tag = null){
		$this->limit = 4;
		if(file_exists(FrontController::themePath() . '/HomePage.php')){
			require(FrontController::themePath() . '/HomePage.php');
			$home_page = new HomePage();
			if(method_exists($home_page, 'getLimit')){
				$this->limit = $home_page->getLimit();
			}
		}
		$page = $id;
		$home_page_post_id = self::getPreference('home_page_post_id');
		if($sort_by === null || strlen($sort_by) === 0){
			$sort_by = 'post_date';
		}
		
		if($sort_by_direction === null || strlen($sort_by_direction) === 0){
			$sort_by_direction = 'desc';
		}
		
		if($page === null || strlen($page) === 0){
			$page = 1;
		}

		$this->sort_by = $sort_by;
		$this->page = $page;
		$this->sort_by_direction = $sort_by_direction;
		if($direction !== null){
			switch($direction){
				case('Previous'):
					$this->page++;
					break;
				case('Back to Top'):
					$this->page--;
					break;
				default:
					$this->page = $page;
					break;
			}
		}

		$view = 'post/index';
		if($this->page < 1){
			$this->page = 1;
		}	
		if($tag == null){
			if($home_page_post_id != null){
				$this->post = Post::findHomePage($home_page_post_id);
				$view = 'post/home';
			}else{
				$this->posts = Post::findPublishedPosts(($this->page-1) * $this->limit, $this->limit, $this->sort_by, $this->sort_by_direction);	
			}
		}else{
			$this->posts = Post::findPublishedByTag(new Tag(array('text'=>$tag)), ($this->page-1) * 5, 5, $this->sort_by, $this->sort_by_direction);	
		}
		
		$this->output = $this->renderView($view, null);
		$this->keywords = implode(', ', String::getKeyWordsFromContent($this->output));
		if($this->post !== null){
			$this->description = $this->post->title;
		}else{
			foreach($this->posts as $post){
				$this->description .= $post->title . ',';
			}
		}
		return $this->renderView('layouts/home', null);
	}
	
}

?>