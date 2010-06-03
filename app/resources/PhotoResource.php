<?php
	class_exists('AppResource') || require('AppResource.php');
	class_exists('Photo') || require('models/Photo.php');
	class PhotoResource extends AppResource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$this->url = FrontController::urlFor(null);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $photos;
		public $url;
		public function put($photo = null){
			if(! AuthController::isAuthorized()){
				throw new Exception(FrontController::UNAUTHORIZED, 401);
			}
			$photo['error_message'] = null;
			$path = $photo['type'];
			$width = 0;
			if(!in_array($photo['type'], array('image/jpg', 'image/jpeg', 'image/gif', 'image/png'))){
				$photo['error_message'] = "I don't accept that type of file.";
			}else{
				$file_type = str_replace(sprintf('image%s', '/'), '', $photo['type']);
				$file_type = String::replace('/jpeg/', 'jpg', $file_type);
				if(is_uploaded_file($photo['tmp_name'])){
					$photo_name = String::replace('/\.*/', '', uniqid(null, true));
					$folder = sprintf('media/%s', date('Y'));
					if(!file_exists($folder)){
						mkdir($folder, 0777, true);
					}
					$folder .= sprintf('/%s', date('n'));

					if(!file_exists($folder)){
						mkdir($folder, 0777, true);
					}
					$folder .= sprintf('/%s', date('j'));
					if(!file_exists($folder)){
						mkdir($folder, 0777, true);
					}
					$path = sprintf('%s/%s.%s', $folder, $photo_name, $file_type);//'media/'.basename($photo['name']);
					$did_move = move_uploaded_file($photo['tmp_name'], $path);
					
					if($did_move === false){
						$photo['error_message'] .= 'Failed to move the photo to ' . $path . '. You should check the folder permissions, making sure it is writable. The error number returned is ' . $photo['error'];
					}else{
						$width = $this->getThumbnailWidth($path);
					}
				}
			}
			/*["name"]=>
		  ["type"]=>
		  string(10) "text/plain"
		  ["tmp_name"]=>
		  string(26) "/private/var/tmp/php7ObsWD"
		  ["error"]=>
		  int(0)
		  ["size"]=>*/

			return $this->renderView('photo/show', array('photo'=>$photo, 'photo_name'=>$photo['name'], 'file_name'=>$photo_name, 'photo_path'=>FrontController::urlFor(null) . $path, 'width'=>$width));
		}
		public function getThumbnailWidth($src){
			$size = getimagesize($src);
			return $size[0] / 4;
		}
		public function getLittleSrc($src){
			return $this->url . $src;
		}
		public function getBigSrc($src){
			return $this->url . $src;
		}
	}

?>