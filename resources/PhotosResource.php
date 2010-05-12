<?php
	class_exists('AppResource') || require('AppResource.php');
	class_exists('Photo') || require('models/Photo.php');
	class PhotosResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$this->url = FrontController::urlFor(null);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $photos;
		public $url;
		public function get(){
			$photo = new Photo();
			$this->photos = $photo->findAll();
			$this->title = "Photo Wall";
			$this->output = $this->renderView('photo/index', null);
			return $this->renderView('layouts/default', null);
		}
	}

?>