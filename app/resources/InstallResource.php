<?php
	class_exists('AppResource') || require('AppResource.php');
    class_exists('Configuration') || require('models/Configuration.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('String') || require('lib/String.php');
	class_exists('Person') || require('models/Person.php');
    class InstallResource extends AppResource{
        public function __construct($attributes = null){
            parent::__construct($attributes);
			if($this->config != null && $this->config->installed){
				$this->redirectTo(null);
			}
        }
        
        public function __destruct(){
            parent::__destruct();
        }
        
        public $configuration;

        public function get(){
			$view = 'install/index';
			if(count($this->url_parts) > 1){
				if($this->url_parts[1] === 'configuration'){
					return $this->get_install_configuration();
				}elseif($this->url_parts[1] === 'done'){
					return $this->get_install_done();
				}
			}
			$this->title = "App Installation";
			$this->output = $this->renderView($view, null);
			return $this->renderView('layouts/install', null);
        }

		public function get_install_configuration(){
			if(!array_key_exists('configuration', $_SESSION)){
				$_SESSION['configuration'] = serialize(new Configuration(array('user_name'=>'user name', 'password'=>'pasword', 'host'=>'localhost', 'prefix'=>'sixd_', 'database'=>'database', 'theme'=>'default', 'db_type'=>'MySql', 'email'=>'graphite@joeyguerra.com')));
			}else if(file_exists('AppConfiguration.php')){
				class_exists('AppConfiguration') || require('AppConfiguration.php');
				$_SESSION['configuration'] = serialize(new AppConfiguration(null));
			}
			$this->title = "Createa a Configuration File";
			$this->configuration = unserialize($_SESSION['configuration']);			
			$this->output = $this->renderView('install/config', null);
			return $this->renderView('layouts/install', null);			
		}
		
		public function get_install_done(){
			$this->title = "Completed Installation";
			$this->output = $this->renderView('install/done', null);
			return $this->renderView('layouts/install', null);
        }

        
		private function createTables($db, Configuration $config){
			$didCreate = true;
			$errors = array();
			if(!$db->exists($config->database)){
				error_log('creating db...');
				$didCreate = $db->createDatabase($config->database);				
			}

			if(!$didCreate){
				$errors[] = 'Failed to create the database.';
			}else{
				$root = str_replace('resources', '', dirname(__FILE__));
				$folder = dir($root . 'models');
				$className = null;
				$reflector = null;
				error_log('installing schema...');
				while(($file = $folder->read()) !== false){
					error_log($file);
					if(preg_match('/^\./', $file) == 0){
						$className = str_replace('.php', '', $file);
						class_exists($className) || require('models/' . $file);
						$reflector = new ReflectionClass($className);
						if($reflector->hasMethod('install')){
							$model = $reflector->newInstanceArgs(array(null, null));
							try{$model->install($config);}catch(Exception $e){$errors[] = $e->getMessage();}
						}
						if($reflector->hasMethod('upgrade')){
							$model = $reflector->newInstanceArgs(array(null, null));
							try{$model->upgrade($config);}catch(Exception $e){$errors[] = $e->getMessage();}
						}
					}
				}
				
				$folder->close();
				
				if(count($errors) == 0){
					
				}
								
			}
			return $errors;
		}
		private function createHttaccessFile(){
			$htaccess_file = 'htaccess.php';
			require($htaccess_file);
			$virtual_path = String::replace('/\/index\.php/', '/', FrontController::getVirtualPath());
			$htaccess = String::replace('/6d/', $virtual_path, $htaccess);
			$did_write = file_put_contents(FrontController::getRootPath('/.htaccess'), $htaccess);
			return $did_write;
		}
		public function put(Configuration $config){
			$errors = array();
			$_SESSION['configuration'] = serialize($config);
			$this->configuration = $_SESSION['configuration'];
			$db = Factory::get($config->db_type, $config);
			$path = FrontController::getRootPath(null);
			if(!is_writable($path)){
				self::setUserMessage("I was unable to create an httaccess file. I need write access to the folder that you're trying to install 6d.");
				$this->redirectTo('install/configuration');
				return null;
			}
			$this->createHttaccessFile();
			try{
				$db->testConnection();
			}catch(Exception $e){
				$errors[] = $e->getCode() . ':' . $e->getMessage();
			}

			if(count($errors) > 0){
				try{
					$db->createDatabase($config->database);
					$errors = array();
				}catch(Exception $e){
					error_log('error message ' . $db->errorMessage);
				}
			}

			$errors = array_merge($config->validate(), $errors);
			try{
				if(count($errors) == 0){
					$config->site_password = $config->site_password;
					$config->installed = true;
					$config->save(FrontController::getRootPath('/AppConfiguration.php'));
					class_exists('AppConfiguration') || require('AppConfiguration.php');
				}
			}catch(Exception $e){
				$errors[] = $e->getMessage();
			}

			if(count($errors) == 0){
				error_log('installing...');
				$errors = $this->createTables($db, $config);
				error_log('done!');
			}
			if(count($errors) == 0){
				$person = Person::findByEmail($config->email);
				if($person == null){
					$person = new Person(null);
					$person->email = $config->email;
					$person->password = $config->password;
					$person->confirmation_password = $config->password;
					$person->name = $config->email;
					$person->is_approved = true;
					$person->is_owner = true;
					$person->uid = uniqid(null, true);
					$person->session_id = session_id();
					$errors = Person::canSave($person);
					if(count($errors) == 0){
						$person = Person::save($person);
					}
				}
			}

			if(count($errors) > 0){
				$message = $this->renderView('install/error', array('message'=>"The following errors occurred when saving the configuration file. Please resolve and try again.", 'errors'=>$errors));					
				self::setUserMessage($message);
				$this->redirectTo('install/configuration');
			}else{
				unset($_SESSION['configuration']);
				// Create the htaccess file.
				$this->redirectTo('install/done');
			}

        }
        
    }
?>