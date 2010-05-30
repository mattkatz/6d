<?php
	class Table{
		
		public function __construct($name, $db){
			$this->_table = $db->getTable($name);
			$this->_db = $db;
		}
		
		
		private $_table;
		private $_db;
		
		public function name(){
			return $this->_table->name;
		}
		public function setName($value){
			$this->_table->name = $value;
		}
		public function columns(){
			return $this->_table->columns;
		}
		public function setColumns($value){
			$this->_table->columns = $value;
		}
		public function keys(){
			return $this->_table->keys;
		}
		public function setKeys($value){
			$this->_table->keys = $value;
		}
		public function option(){
			return $this->_table->option;
		}
		public function setOption($value){
			$this->_table->option = $value;
		}
		public function db_name(){
			return $this->_table->db_name;
		}
		public function setDb_name($value){
			$this->_table->db_name = $value;
		}
		public function rows(){
			return $this->_table->rows;
		}
		public function created(){
			return $this->_table->created;
		}
		public function modified(){
			return $this->_table->modified;
		}
		public function comment(){
			return $this->_table->comment;
		}
		public function setComment($value){
			$this->_table->comment = $value;
		}
		
		public function addColumn($name, $type, $options){
			$this->_table->addColumn($name, $type, $options);
		}
		public function addKey($type, $options){
			$this->_table->addKey($type, $options);
		}
		public function addOption($value){
			$this->_table->addOption($value);
		}
		public function get_column($name){
			if($name != null){
				if($this->_db->tableExists($this->_table->name) && $this->_db->columnExists($this->_table->name, $name)){
					$columns = $this->_db->getColumns($this->_table->name);
					foreach($columns as $column){
						if($column->Field == $name){
							return $column;
						}
					}
				}
			}
		}
		public function alter_column($name, $type, $options){
			$errors = array();
			if($name != null && $type != null){
				try{
					if($this->_db->tableExists($this->_table->name) && $this->_db->columnExists($this->_table->name, $name)){
						$this->_db->execute($this->_table->get_alter_column_sql($name, $type, $options));
					}
				}catch(Exception $e){
					$errors[] = $e->getMessage();
					$errors[] = $e->getTraceAsString();
				}
			}
			return $errors;
		}
		public function rename_column($old_name, $new_name, $type, $options){
			$errors = array();
			if($old_name != null && $new_name != null){
				try{
					if($this->_db->tableExists($this->_table->name) && $this->_db->columnExists($this->_table->name, $old_name)){
						$this->_db->execute($this->_table->get_rename_column_sql($old_name, $new_name, $type, $options));
					}
				}catch(Exception $e){
					$errors[] = $e->getMessage();
					$errors[] = $e->getTraceAsString();
				}
			}
			return $errors;
		}
		public function add_column($name, $type, $options){
			$errors = array();
			if($name != null && $type != null){
				try{
					if($this->_db->tableExists($this->_table->name) && !$this->_db->columnExists($this->_table->name, $name)){
						$this->_db->execute($this->_table->get_add_column_sql($name, $type, $options));
					}
				}catch(Exception $e){
					$errors[] = $e->getMessage();
					$errors[] = $e->getTraceAsString();
				}
			}
			return $errors;
		}
		public function save(){
			$errors = array();
			try{
				if(!$this->_db->tableExists($this->_table->name)){
					$this->_db->execute($this->_table->getSql());
				}
			}catch(Exception $e){
				$errors[] = $e->getMessage();
				$errors[] = $e->getTraceAsString();
			}
			return $errors;
		}
		public function exists($name){
			return $this->_db->tableExists($name);
		}
		
	}
?>