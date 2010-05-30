<?php
class_exists('AppResource') || require('AppResource.php');
class BookmarkletResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	public function get(){
		if(count($this->url_parts) > 1){
			return $this->{'get' . uwcase($this->url_parts[1])}();
		}
	}
	public function getDelicious(){
		$view = 'bookmarklet/delicious';
		$this->output = $this->renderView($view, null);
		return $this->renderView('layouts/home', null);
	}
	public function getTwitter(){
		$view = 'bookmarklet/twitter_translate';
		$this->output = $this->renderView($view, null);
		return $this->renderView('layouts/home', null);
	}
}