<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('Post') || require('Post.php');
	class Friend extends Object{
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

		private $email;
		public function getEmail(){
			return $this->email;
		}
		public function setEmail($val){
			$this->email = $val;
		}
		
		private $url;
		public function getUrl(){
			return $this->url;
		}
		public function setUrl($val){
			$this->url = $val;
		}

		private $is_approved;
		public function getIsApproved(){
			return $this->is_approved;
		}
		public function setIsApproved($val){
			$this->is_approved = $val;
		}

		private $public_key;
		public function getPublicKey(){
			return $this->public_key;
		}
		public function setPublicKey($val){
			$this->public_key = $val;
		}

		private $profile;
		public function getProfile(){
			return $this->profile;
		}
		public function setProfile($val){
			$this->profile = $val;
		}

		private $time;
		public function getTime(){
			return $this->time;
		}
		public function setTime($val){
			$this->time = $val;
		}
		
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'friends';
		}
		
		public static function stringify($text){
			return sprintf("%s", urlencode($text));
		}
		
		public static function delete_many($ids){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			if($ids !== null){
				$ids = array_map(array('Friend', 'stringify'), $ids);
				$clause = new ByClause(sprintf("id in (%s)", implode(',', $ids)), null, 0, null);
				return $db->delete($clause, new Friend(null));
			}
			return 0;
		}
		public static function delete(Friend $friend){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			return $db->delete(null, $friend);
		}
		public static function save(Friend $friend){
			$errors = self::canSave($friend);
			$config = new AppConfiguration();
			NotificationCenter::getInstance()->postNotificationName('LogEventHasOccurred', 'saving friend : ' . $friend->email, $friend);
			$new_friend = null;
			if(count($errors) == 0){
				$db = Factory::get($config->db_type, $config);
				$new_friend = $db->save(null, $friend);
				$friend->id = $new_friend->id;
				self::notify('didSaveFriend', $friend, $friend);
			}
			return array($new_friend, $errors);
		}
		
		public static function findAll(){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All(null, null, 0, null), new Friend());
			return $list;
		}
		public static function findByTagText($text){
			$config = new AppConfiguration();
			$friend = new Friend(null);
			$db = Factory::get($config->db_type, $config);
			$tag = new Tag(null);
			$query = sprintf("select f.* from {$friend->getTableName()} as f, {$tag->getTableName()} as t where t.type='group' and t.parent_id=f.id and t.text = '%s'", self::stringify($text));
			$list = $db->find(new All($query, null, 0, array('id'=>'asc')), $friend);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findById($id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ById($id), new Friend());
			return $list;
		}
		
		public static function findByEmail($email){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByAttribute('email', urlencode($email), 1, null), new Friend());
			return $list;
		}
		public static function findByUrl($url){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByAttribute('url', urlencode($url), 1, null), new Friend());
			return $list;
		}
		public static function findByPublicKey($public_key){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByAttribute('public_key', urlencode($public_key), 1, null), new Friend());
			return $list;
		}
		public static function sort_by_name($a, $b){
			$a_name = strtolower($a->name);
			$b_name = strtolower($b->name);
			if($a_name == $b_name){
				return 0;
			}
			return ($a_name < $b_name) ? -1 : 1;
		}
		
		public static function canSave(Friend $friend){
			$errors = array();

			/*if(empty($friend->email)){
				$errors[] = 'Email is required.';
			}
			if(empty($friend->url)){
				$errors[] = 'Url is required.';
			}*/
			
			return $errors;
		}
		
		/*public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			error_log('installing friends');
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'biginteger', array('is_nullable'=>false, 'auto_increment'=>true));
				$table->addColumn('email', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
				$table->addColumn('name', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
				$table->addColumn('url', 'string', array('is_nullable'=>true, 'default'=>null, 'size'=>255));
				$table->addColumn('public_key', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('is_approved', 'boolean', array('is_nullable'=>true, 'default'=>0));
				$table->addColumn('profile', 'text', array('is_nullable'=>true));
				$table->addColumn('time', 'datetime', array('is_nullable'=>false));
				$table->addKey('primary', 'id');
				$table->addKey('key', array('url'=>'url'));
				$table->addKey('key', array('email'=>'email'));
				$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
				$errors = $table->save();
				if(count($errors) > 0){
					foreach($errors as $error){
						$message .= $error;
					}
					throw new Exception($message);
				}
			}catch(Exception $e){
				$db->deleteTable($this->tableName);
				throw $e;
			}
		}*/
		
	}
?>