<?php
	class Factory{
		
		public static function get($name, $args){
			$root = str_replace('Factory.php', '', __FILE__);
			$folder = dir($root);

			while (false !== ($entry = $folder->read())){
				$path = $root . $entry . '/' . $name . '.php';
				if(file_exists($path)){
					class_exists($name) || require($path);
					return new $name($args);
				}
			}
			
			$folder->close();
			return new $name();
		}
	}
?>