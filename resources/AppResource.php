<?php
	class_exists('Resource') || require('lib/Resource.php');
	class_exists('UserResource') || require('resources/UserResource.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('Post') || require('models/Post.php');
	class_exists('Person') || require('models/Person.php');
	class_exists('Setting') || require('models/Setting.php');
	class_exists('NotificationCenter') || require('lib/NotificationCenter.php');
	class AppResource extends Resource{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			Post::addObserver($this, 'willReturnValueForKey', 'Post');
			$resource_name = strtolower(str_replace('Resource', '', get_class($this)));				
			$this->resource_css = $resource_name . '.css';
			$this->resource_js = $resource_name . '.js';
			$root = str_replace('resources', '', dirname(__FILE__));
			if(file_exists($root . FrontController::themePath() . '/js/' . $this->resource_js)){
				$this->resource_js = FrontController::urlFor('themes') . 'js/' . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}elseif(file_exists($root . 'js/' . $this->resource_js)){
				$this->resource_js = FrontController::urlFor('js') . $this->resource_js;
				$this->resource_js = $this->to_script_tag('text/javascript', $this->resource_js);
			}else{
				$this->resource_js = null;
			}
			if(file_exists(FrontController::themePath() . '/css/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('themes') . 'css/' . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}elseif(file_exists($root . 'css/' . $this->resource_css)){
				$this->resource_css = FrontController::urlFor('css') . $this->resource_css;
				$this->resource_css = $this->to_link_tag('stylesheet', 'text/css', $this->resource_css, 'screen,projection');
			}else{
				$this->resource_css = null;
			}

			if(!class_exists('AppConfiguration')){
				if(get_class($this) != 'InstallResource'){					
					FrontController::redirectTo('install', null);
				}
			}else{
				$this->config = new AppConfiguration();
				try{
					$this->settings = Setting::findAll();
					$this->owner = Person::findOwner();
					$this->owner->profile = unserialize($this->owner->profile);
					$this->title = $this->owner->profile->site_name;
					$theme_path = FrontController::getAppPath() . '/' . FrontController::themePath() . '/ThemeController.php';
					if(file_exists($theme_path)){
						require($theme_path);
						$this->theme = new ThemeController($this);
					}
				}catch(Exception $e){}
			}
		}
		
		public function __destruct(){
			parent::__destruct();
		}
		public $owner;
		public $show_notes;
		public $notes;
		public $theme;
		public $resource_css;
		public $resource_js;
		protected $settings;
		protected $config;
		protected static $error_html;
		public function willReturnValueForKey($key, $obj, $val){
			return $val;
		}
		public function to_link_tag($rel, $type, $url, $media){
			return sprintf('<link rel="%s" type="%s" href="%s" media="%s" />', $rel, $type, $url, $media);
		}
		public function to_script_tag($type, $url){
			return sprintf('<script type="%s" src="%s"></script>', $type, $url);
		}

		public function getHome_page_post_id(){
			if($this->settings != null){
				foreach($this->settings as $setting){
					if($setting->name == 'home_page_post_id'){
						return $setting->value;
					}
				}
			}
			return 0;
		}
		
		public function didFinishLoading(){
			parent::didFinishLoading();
		}
		public function hasRenderedOutput($layout, $output){
			if(self::$error_html != null){
				$output .= self::$error_html;
			}
			//error_log('request uri ' . $layout . ' ' . $_SERVER['REQUEST_URI']);
			$output = $this->filterFooter($output);
			return $output;
		}		
		protected function filterBody($text){
			$post_filters = $this->getPlugins('plugins', 'PostPlugin');
			foreach($post_filters as $filter){
				$text = $filter->execute($text);
			}
			return $text;
		}
		
		private function filterFooter($output){
			$filters = $this->getPlugins('filters', 'FooterFilter');
			foreach($filters as $filter){
				$output = $filter->execute($output);
			}
			return $output;
		}
		
		protected function getPlugins($folder_name, $name){
			$files = $this->getFiles($folder_name, $name);
			$plugins = array();
			foreach($files as $file){
				$parts = explode('/', $file);
				$class_name = array_pop($parts);
				$class_name = str_replace('.php', '', $class_name);
				class_exists($class_name) || require($file);
				$plugins[] = new $class_name();
			}
			return $plugins;
		}
		private function getFiles($folder_name, $name){
			$root = FrontController::getDocumentRoot() . FrontController::getVirtualPath() . '/' . $folder_name;
			$folders = $this->getFolders($root);
			$plugin_paths = array();
			foreach($folders as $folder){
				$dir = dir($folder);
				while(($entry = $dir->read()) !== false){
					if(strpos($entry, '.') !== 0){
						$file_name = $dir->path . '/' . $entry;
						if(!is_dir($file_name) && stripos($entry, $name . '_') !== false){
							$plugin_paths[] = $file_name;
						}
					}
				}
			}
			return $plugin_paths;
		}
		private function getFolders($path){
			$folders = array();
			$folder = dir($path);
			if($folder !== false){
				while(($entry = $folder->read()) !== false){
					if(strpos($entry, '.') !== 0){
						$file_name = $folder->path .'/'. $entry;
						if(is_dir($file_name)){
							$folders[] = $file_name;
						}
					}
				}
			}
			return $folders;
		}
		public function getTitleFromOutput($output){
			$matches = array();
			preg_match ( '/\<h1\>.*\<\/h1\>/' , $output, $matches);
			if(count($matches) > 0){
				return String::stripHtmlTags($matches[0]);
			}else{
				return null;
			}
		}
		
		public function getPreference($name){
			if($this->settings != null){
				foreach($this->settings as $setting){
					if($name == $setting->name){
						return $setting->value;
					}
				}				
			}
			return null;
		}
		public static function randomIndexWithWeights($weights) {
		    $r = mt_rand(1,1000);
		    $offset = 0;
		    foreach ($weights as $k => $w) {
		        $offset += $w*1000;
		        if ($r <= $offset) {
		            return $k;
		        }
		    }
		}
	}
?>