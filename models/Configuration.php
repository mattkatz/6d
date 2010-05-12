<?php
    class_exists('Object') || require('lib/Object.php');
    class Configuration extends Object{
        public function __construct($attributes = null){
            parent::__construct($attributes);
        }
        
        public function __destruct(){
        
            parent::__destruct();
        }
        
        private $user_name;
		public function getUser_name(){
			return $this->user_name;
		}
		public function setUser_name($val){
			$this->user_name = $val;
		}
		
        private $password;
		public function getPassword(){
			return $this->password;
		}
		public function setPassword($val){
			$this->password = $val;
		}
		
        private $host;
		public function getHost(){
			return $this->host;
		}
		public function setHost($val){
			$this->host = $val;
		}
        
 		private $database;
		public function getDatabase(){
			return $this->database;
		}
		public function setDatabase($val){
			$this->database = $val;
		}

        private $prefix;
		public function getPrefix(){
			return $this->prefix;
		}
		public function setPrefix($val){
			$this->prefix = $val;
		}

		private $db_type;
		public function getDb_type(){
			return $this->db_type;
		}
		public function setDb_type($val){
			$this->db_type = $val;
		}
		
		private $theme;
		public function getTheme(){
			return $this->theme;
		}
		public function setTheme($val){
			$this->theme = $val;
		}
		
		private $email;
		public function getEmail(){
			return $this->email;
		}
		public function setEmail($val){
			$this->email = $val;
		}

		private $site_password;
		public function getSite_password(){
			return $this->site_password;
		}
		public function setSite_password($val){
			$this->site_password = $val;
		}
		
		private $ssl_path;
		public function getSslPath(){
			return $this->ssl_path;
		}
		public function setSslPath($val){
			$this->ssl_path = $val;
		}

		private $site_path;
		public function getSite_path(){
			return $this->site_path;
		}
		public function setSite_path($val){
			$this->site_path = $val;
		}
		
		
		private $installed;
		public function getInstalled(){
			return $this->installed;
		}
		public function setInstalled($val){
			$this->installed = $val;
		}
		
		public function validate(){
			$errors = array();
			echo $this->user_name;
			if(!isset($this->user_name)){
				$errors[] = 'User name is required.';
			}
			
			if(!isset($this->password)){
				$errors[] = 'Password is required.';
			}
			
			if(!isset($this->site_password)){
				$errors[] = 'Site Password is required.';
			}elseif(strlen($this->site_password) < 5){
				$errors[] = 'You gotta make your password bigger than 5 characters.';
			}
			
			if(!isset($this->host)){
				$errors[] = 'Host is required.';
			}
			
			return $errors;
		}
		
		public function save($location){
			$text = <<<eos
<?php
	class AppConfiguration{
		public function __construct(\$attributes = null){
			//PLACE_HOLDER
        }
        public function __destruct(){}
        public \$user_name;		
        public \$password;		
        public \$host;
 		public \$database;
        public \$prefix;
		public \$db_type;
		public function getTheme(){
			return \$_SESSION['theme'];
		}
		public function setTheme(\$val){
			\$_SESSION['theme'] = \$val;
		}
		
		public \$email;
		public \$site_password;
		public \$ssl_path;
		public \$site_path;		
		public \$installed;
	}
?>
eos;
			$text = $this->map($this, $text);
			if($text != null){				
				$result = file_put_contents($location, $text);				
				if($result === false){
					throw new Exception("Failed to create the configuration file. Please make the install directory writable. You might want to set the permissions back to what they were after installation.", 500);
				}
				chmod($location, 0755);
			}
		}
		
		public function map($obj, $text){
			$pos = strpos($text, '//PLACE_HOLDER');
			if($pos !== false){
				$first = substr($text, 0, $pos-1);
				$last = substr($text, $pos, strlen($text)-1);
				$middle = '	$this->user_name = \''. $obj->user_name . '\';
			$this->password = \''. $obj->password . '\';
			$this->host = \''. $obj->host . '\';
			$this->database = \''. $obj->database . '\';
			$this->prefix = \''. $obj->prefix . '\';
			$this->db_type = \''. $obj->db_type . '\';
			$this->email = \''. $obj->email . '\';
			$this->setTheme(\''. $obj->theme . '\');
			$this->site_password = \''. $obj->site_password . '\';
			$this->ssl_path = \''. $obj->ssl_path . '\';
			$this->site_path = \''. $obj->site_path . '\';
			$this->installed = '. ($obj->installed ? 'true' : 'false') . ';
			';
				$text = $first . $middle . $last;
				return $text;
			}
			return null;
		}
    }
?>