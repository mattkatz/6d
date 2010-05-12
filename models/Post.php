<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('Tag') || require('Tag.php');
	class Post extends Object{
		public function __construct($attributes = null){
			parent::__construct($attributes);
			$this->type = 'post';
			$this->is_published = false;
		}
		public function __destruct(){
			parent::__destruct();
		}
		private $date;
		public function getDate(){
			return $this->date;
		}
		private $id;
		public function getId(){
			return $this->id;
		}
		public function setId($val){
			$this->id = $val;
		}

		private $person_post_id;
		public function getPerson_post_id(){
			return $this->person_post_id;
		}
		public function setPerson_post_id($val){
			$this->person_post_id = $val;
		}

		private $title;
		public function getTitle(){
			return $this->title;
		}
		public function setTitle($val){
			$this->title = $val;
		}

		private $type;
		public function getType(){
			return $this->type;
		}
		public function setType($val){
			$this->type = $val;
		}

		private $body;
		public function getBody(){
			return $this->body;
		}
		public function setBody($val){
			$this->body = $val;
		}

		private $source;
		public function getSource(){
			return $this->source;
		}
		public function setSource($val){
			$this->source = $val;
		}
		
		private $url;
		public function getUrl(){
			return $this->url;
		}
		public function setUrl($val){
			$this->url = $val;
		}

		private $description;
		public function getDescription(){
			return $this->description;
		}
		public function setDescription($val){
			$this->description = $val;
		}

		private $created;
		public function getCreated(){
			return $this->created;
		}
		public function setCreated($val){
			$this->created = $val;
		}
		private $post_date;
		public function getPost_date(){
			return $this->post_date;
		}
		public function setPost_date($val){
			$this->post_date = $val;
		}

		private $custom_url;
		public function getCustom_url(){
			return $this->custom_url;
		}
		public function setCustom_url($val){
			$this->custom_url = $val;
		}

		private $is_published;
		public function getIs_published(){
			return $this->is_published;
		}
		public function setIs_published($val){
			$this->is_published = $val;
		}

		private $tags;
		public function getTags(){
			return $this->tags;
		}
		public function setTags($val){
			$this->tags = $val;
		}
		
		public function isHomePage($home_page_post_id){
			return $this->id > 0 && $this->id == $home_page_post_id;
		}
		// I need a way to tell the data storage whether or not to add the id in the sql statement
		// when inserting a new record. This is it. The data storage should default it to false, so
		// if this method doesn't exist, it'll default to false.
		public function shouldInsertId(){
			return true;
		}
		public function willAddFieldToSaveList($name, $value){
			
			if($name === 'id' && ($this->id === null || strlen($this->id) === 0)){
				return uniqid(null, true);
			}
			return $value;			
		}
		public static function findAll(){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All(null, null, 0, null), new Post(null));
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublished($start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$list = $db->find(new ByAttribute('is_published', true, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublishedPosts($start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$list = $db->find(new ByClause("is_published=1 and type != 'page'", null, array($start, $limit), array($sort_by=>$sort_by_direction, 'id'=>'desc')), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findByPerson(Person $person, $start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$start_limit = null;
			if($limit > 0){
				$start_limit = array($start, $limit);
			}else{
				$start_limit = $limit;
			}
			$list = $db->find(new ByClause(sprintf("source = %s", ($person->url === null ? "''" : "'" . $person->url . "'")), null, $start_limit, array($sort_by=>$sort_by_direction)), $post);
			
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findByTag($tag, $start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$tag->text = urlencode($tag->text);
			$list = $db->find(new ByClause("tags like '%{$tag->text}%'", null, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findPublishedByTag($tag, $start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$tag->text = urlencode($tag->text);
			$list = $db->find(new ByClause("tags like '%{$tag->text}%' and is_published=1", null, array($start, $limit), array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : $list);
			return $list;
		}

		public static function find($start, $limit, $sort_by, $sort_by_direction = 'desc'){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			if($sort_by === null || strlen($sort_by) === 0){
				$sort_by = $post->getTableName() . '.id';
			}
			$start_limit = null;
			if($limit > 0){
				$start_limit = array($start, $limit);
			}else{
				$start_limit = $limit;
			}
			$list = $db->find(new All(null, null, $start_limit, array($sort_by=>$sort_by_direction)), $post);
			$list = ($list == null ? array() : (is_array($list) ? $list : array($list)));
			return $list;
		}
		public static function findPublishedPages(){
			$config = new AppConfiguration();
			$post = new Post(null);
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByClause("type='page' and is_published=1", null, 0, null), $post);
			return $list;
		}
		public static function findAllPublished($custom_url){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ByClause(sprintf("is_published=1 and custom_url='%s'", $custom_url), null, 1, null), new Post(null));
			return $post;
		}
		
		public static function findByAttribute($name, $value){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ByClause(sprintf("%s='%s'", $name, $value), null, 1, null), new Post(null));
			return $post;
		}
	
		public static function findById($id = null){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ById($id), new Post(null));
			return $post;
		}
		public static function findHomePage($id = null){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ByClause(sprintf("id='%s' and is_published=1", $id), null, 1, null), new Post(null));
			return $post;
		}
		
		public static function findByPersonPostId($id = 0){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$post = $db->find(new ByAttribute('person_post_id', $id, 1, null), new Post(null));
			return $post;
		}
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'posts';
		}
		public static function delete(Post $post){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			return $db->delete(null, $post);
		}
		public static function save(Post $post){
			$errors = self::canSave($post);
			$config = new AppConfiguration();
			if(count($errors) == 0){
				$db = Factory::get($config->db_type, $config);
				$post->custom_url = String::stringForUrl($post->title);
				$new_post = $db->save(null, $post);
				$post->id = $new_post->id;
				$tags = String::explodeAndTrim($post->tags);
				$existing_tags = Tag::findAllForPost($post->id);
				if($existing_tags != null){
					foreach($existing_tags as $tag){
						Tag::delete($tag);
					}
				}

				foreach($tags as $tag_text){
					if($existing_tags == null || !in_array($tag_text, $existing_tags)){
						Tag::save(new Tag(array('parent_id'=>$post->id, 'type'=>'post', 'text'=>$tag_text)));
					}
				}
				self::notify('didSavePost', $post, $post);
			}
			return array($post, $errors);
		}

		public static function canSave(Post $post){
			$errors = array();
			return $errors;
		}
		public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'string', array('is_nullable'=>false, 'size'=>255));
				$table->addColumn('person_post_id', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('title', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('type', 'string', array('is_nullable'=>true, 'default'=>'post', 'size'=>80));
				$table->addColumn('body', 'text', array('is_nullable'=>true, 'default'=>''));
				$table->addColumn('source', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('url', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('description', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('post_date', 'datetime', array('is_nullable'=>true, 'default'=>null));
				$table->addColumn('created', 'datetime', array('is_nullable'=>false));
				$table->addColumn('custom_url', 'string', array('is_nullable'=>true, 'default'=>'', 'size'=>255));
				$table->addColumn('tags', 'text', array('is_nullable'=>true));
				$table->addColumn('is_published', 'boolean', array('is_nullable'=>true, 'default'=>false));
				
				$table->addKey('primary', 'id');
				$table->addKey('key', array('title_key'=>'title'));
				$table->addKey('key', array('custom_url_key'=>'custom_url'));
				$table->addKey('key', array('is_published_key'=>'is_published'));
				$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
				$errors = $table->save();
				if(count($errors) > 0){
					foreach($errors as $error){
						$message .= $error;
					}
					throw new Exception($message);
				}
			}catch(Exception $e){
				$db->deleteTable($this->getTableName($config));
				throw $e;
			}
		}
		
	}
?>