<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class Setting extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
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

		private $name;
		public function getName(){
			return $this->name;
		}
		public function setName($val){
			$this->name = $val;
		}

		private $value;
		public function getValue(){
			return $this->value;
		}
		public function setValue($val){
			$this->value = $val;
		}

		private $timestamp;
		public function getTimestamp(){
			return $this->timestamp;
		}
		public function setTimestamp($val){
			$this->timestamp = $val;
		}
		
		public static function findAll(){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All(null, null, 0, null), new Setting(null));
			return $list;
		}
		
		public static function findByName($name){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$setting = $db->find(new ByAttribute('name', $name, 1, null), new Setting(null));
			return $setting;
		}
		
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'settings';
		}
		public static function delete($key){
			if($key != null){
				$config = new AppConfiguration();
				$db = Factory::get($config->db_type, $config);
				$setting = Setting::findByName($key);
				$setting->value = null;
				return Setting::save($setting);
			}
			return null;
		}
		public static function save(Setting $preference){
			$errors = self::canSave($preference);
			$config = new AppConfiguration();
			if(count($errors) == 0){
				$db = Factory::get($config->db_type, $config);
				$new_preference = $db->save(null, $preference);
				$preference->id = $new_preference->id;
				self::notify('didSaveSetting', $preference, $preference);
			}
			return array('preference'=>$preference, 'errors'=>$errors);
		}
		
		public static function canSave(Setting $preference){
			$errors = array();
			if($preference->name == null || strlen($preference->name) == 0){
				$errors['name'] = "Preference name is required.";
			}
			return $errors;
		}
		
		private $list;
		public function add(Setting $preference){
			if($this->list == null){
				$this->list = array();
			}
			$this->list[] = $preference;
		}
		
		public function saveAll(){
			if($this->list != null && count($this->list) > 0){
				foreach($this->list as $preference){
					try{
						self::save($preference);
					}catch(Exception $e){
						error_log($e);
					}
				}
			}
		}
		
		private function add_default_values(){
			if(Setting::findByName('home_page_post_id') == null){
				$this->add(new Setting(array('name'=>'home_page_post_id', 'value'=>null)));				
			}
			$this->saveAll();
		}
		public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'integer', array('is_nullable'=>false, 'auto_increment'=>true));
				$table->addColumn('name', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('value', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('timestamp', 'timestamp', array('is_nullable'=>false));		
				
				$table->addKey('primary', 'id');
				$table->addKey('unique', array('name_key'=>'name'));
				$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
				
				// WE need to include the appconfiguration file that was saved when installing
				// because this save method requires it.
				$errors = $table->save();
				if(count($errors) > 0){
					foreach($errors as $error){
						$message .= $error;
					}
					throw new Exception($message);
				}
				$this->add_default_values();
			}catch(Exception $e){
				$db->deleteTable($this->getTableName($config));
				throw $e;
			}
		}
		
	}
?>