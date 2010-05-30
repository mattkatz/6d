<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('Post') || require('Post.php');
	class Photo extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$this->is_published = false;
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		private $id;
		public function getId(){
			return $this->id;
		}
		public function setId($val){
			$this->id = $val;
		}

		private $post_id;
		public function getPostId(){
			return $this->post_id;
		}
		public function setPostId($val){
			$this->post_id = $val;
		}

		private $src;
		public function getSrc(){
			return $this->src;
		}
		public function setSrc($val){
			$this->src = $val;
		}

		private $title;
		public function getTitle(){
			return $this->title;
		}
		public function setTitle($val){
			$this->title = $val;
		}

		private $description;
		public function getDescription(){
			return $this->description;
		}
		public function setDescription($val){
			$this->description = $val;
		}

		private $album;
		public function getAlbum(){
			return $this->album;
		}
		public function setAlbum($val){
			$this->album = $val;
		}

		private $tags;
		public function getTags(){
			return $this->tags;
		}
		public function setTags($val){
			$this->tags = $val;
		}

		private $timestamp;
		public function getTimestamp(){
			return $this->timestamp;
		}
		public function setTimestamp($val){
			$this->timestamp = $val;
		}
		
		// I need a way to tell the data storage whether or not to add the id in the sql statement
		// when inserting a new record. This is it. The data storage should default it to false, so
		// if this method doesn't exist, it'll default to false.
		public function shouldInsertId(){
			return true;
		}
		public function willAddFieldToSaveList($name, $value){
			
			if($name == 'id' && ($this->id === null || strlen($this->id) === 0)){
				return uniqid(null, true);
			}
			return $value;
		}
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'photos';
		}
		
		public function findAll($path = null){
			$root = ($path == null ? 'media' : $path);
			$folder = dir($root);
			self::$images = array();
			$this->traverse($root);
			/*while (false !== ($entry = $folder->read())){
				if(strpos($entry, '.') !== 0){
					$file_name = $folder->path .'/'. $entry;
					$images = array_push($images, $this->traverse($file_name));
				}
			}
			$folder->close();
			*/
			return self::$images;
		}
		
		private static $images;
		private function traverse($path){
			$root = ($path == null ? 'media' : $path);
			if(!file_exists($root)){
				mkdir($root, 0777);
			}
			$folder = dir($root);
			$images = array();
			if($folder != null){
				while (false !== ($entry = $folder->read())){
					if(strpos($entry, '.') !== 0){
						$file_name = $folder->path .'/'. $entry;					
						if(is_dir($file_name)){
							$this->traverse($file_name);						
						}else{						
							self::$images[] = new Photo(array('src'=>$file_name));
						}
					}
				}
				$folder->close();
			}
		}
		
		private function findFileInFolder($file){
			$folder = dir($file);
			while(false !== ($entry = $folder->read())){
				if(is_file($entry)){
					return $entry;
				}else{
					return $this->findFileInFolder($entry);
				}
			}
		}
	}
?>