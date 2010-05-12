<?php
	class_exists('Object') || require('lib/Object.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	class_exists('TagWithWeight') || require('TagWithWeight.php');
	class Tag extends Object{
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

		private $parent_id;
		public function getParent_id(){
			return $this->parent_id;
		}
		public function setParent_id($val){
			$this->parent_id = $val;
		}
		
		private $type;
		public function getType(){
			return $this->type;
		}
		public function setType($val){
			$this->type = $val;
		}

		private $text;
		public function getText(){
			return $this->text;
		}
		public function setText($val){
			$this->text = $val;
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
			$list = $db->find(new All(null, null, 0, null), new Tag(null));
			$list = ($list == null ? array() : $list);
			return $list;
		}
		public static function findAllTagsForPosts(){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new All("select text, type, count(id) as weight from sixd_tags where type='post' group by text", null, 0, array('text'=>'asc')), new TagWithWeight(new Tag()));
			$list = ($list == null ? array() : $list);
			return $list;
		}
		
		public static function findAllTagsForGroups(){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$tag = new Tag();
			$list = $db->find(new All("select text, count(text) -1 as count from {$tag->getTableName()} where type='group' group by text", null, 0, array('text'=>'asc')), $tag);
			$list = ($list == null ? array() : $list);
			return $list;
		}
		
		public static function findById($id){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			$self_name = __CLASS__;
			$list = $db->find(new ById($id), new $self_name());
			return $list;
		}
		public static function findGroupTagsByText($text){
			$clause = null;
			if(is_array($text)){
				$text = array_map(array('Tag', 'stringify'), $text);
				$clause = new ByClause(sprintf("type='group' and text in (%s)", implode(',', $text)), null, 0, array('text'=>'asc'));
			}else{
				$clause = new ByClause(sprintf("type='group' and text=%s", self::stringify($text)), null, 0, array('text'=>'asc'));				
			}
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find($clause, new Tag());
			return $list;
		}
		
		public static function findTagsByTextAndParent_id($text, $parent_id){
			$clause = sprintf("text='%s' and parent_id='%s'", urlencode($text), $parent_id);
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByClause($clause, null, 0, null), new Tag());
			return $list;
		}
		public static function findTagsByTextAndParents($text, $ids){
			$ids = array_map('intval', $ids);
			$clause = new ByClause(sprintf("text='%s' and parent_id in (%s)", $text, implode(',', $ids)), null, 0, null);
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$list = $db->find(new ByClause($clause, null, 0, null), new Tag());
			return $list;
		}
		
		public static function findAllForPost($parent_id){
			$config = new AppConfiguration();				
			$db = Factory::get($config->db_type, $config);
			$parts = array();
			$clause = sprintf("parent_id='%s' and type='%s'", $parent_id, 'post');
			$list = $db->find(new ByClause($clause, null, 0, null), new Tag(null));
			$list = ($list == null ? null : (is_array($list) ? $list : array($list)));
			return $list;
		}
		

		public static function stringify($item){
			return sprintf("'%s'", urlencode($item));
		}
		
		public static function pluck_text($tag){
			return $tag->text;
		}
		public static function pluck($name, $list){
			$new_list = array();
			foreach($list as $tag){
				$new_list[] = $tag->{$name};
			}
			return $new_list;
		}
		
		public function getTableName($config = null){
			if($config == null){
				$config = new AppConfiguration();
			}
			return $config->prefix . 'tags';
		}

		public static function save(Tag $tag){
			$errors = self::canSave($tag);
			$config = new AppConfiguration();
			
			if(count($errors) == 0){
				$db = Factory::get($config->db_type, $config);
				$tag->text = $tag->text;
				$new_tag = $db->save(null, $tag);
				$tag->id = $new_tag->id;
				self::notify('didSaveTag', $tag, $tag);
			}
			return array($tag, $errors);
		}
		
		public static function canSave(Tag $tag){
			$errors = array();
			if($tag->text == null || strlen($tag->text) == 0){
				$errors['text'] = "Tag text is required.";
			}

			if($tag->type == null || strlen($tag->type) == 0){
				$errors['type'] = "Tag type is required.";
			}

			return $errors;
		}
		public static function delete_many($type, $tags){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			if($tags !== null){				
				$text = array_map(array('Tag', 'stringify'), $tags);
				$clause = new ByClause(sprintf("type='%s' and text in (%s)", $type, implode(',', $text)), null, 0, array('text'=>'asc'));
				return $db->delete($clause, new Tag(null));
			}
		}
		public static function delete(Tag $tag){
			$config = new AppConfiguration();
			$db = Factory::get($config->db_type, $config);
			if(($tag->id === null || strlen($tag->id) === 0) && $tag->text !== null){
				$tag->id = null;
				return $db->delete(new ByClause(sprintf("text='%s'", urlencode($tag->text)), null, 1, null), $tag);
			}else{
				return $db->delete(null, $tag);				
			}
		}
		
		public function install(Configuration $config){
			$message = '';
			$db = Factory::get($config->db_type, $config);
			try{
				$table = new Table($this->getTableName($config), $db);
				$table->addColumn('id', 'biginteger', array('is_nullable'=>false, 'auto_increment'=>true));
				$table->addColumn('parent_id', 'string', array('is_nullable'=>true, 'size'=>255, 'default'=>null));
				$table->addColumn('type', 'string', array('is_nullable'=>false, 'size'=>80));
				$table->addColumn('text', 'string', array('is_nullable'=>true, 'size'=>255));
				$table->addColumn('timestamp', 'timestamp', array('is_nullable'=>false));		
				
				$table->addKey('primary', 'id');
				$table->addKey('key', array('parent_id_key'=>'parent_id'));
				$table->addKey('key', array('type_key'=>'type'));
				$table->addKey('key', array('text_key'=>'text'));
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