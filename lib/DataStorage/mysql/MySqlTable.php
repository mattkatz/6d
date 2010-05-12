<?php
	class_exists('MySqlColumn') || require('lib/DataStorage/mysql/MySqlColumn.php');
	class_exists('MySqlKey') || require('lib/DataStorage/mysql/MySqlKey.php');
	class MySqlTable{
		public $name;
		public $columns;
		public $keys;
		public $option;
		public function __construct($name){
			$this->name = $name;
		}
		public function __destruct(){}
		public function addColumn($name, $type, $options){
			$this->columns[] = new MySqlColumn($name, $type, $options);
		}
		public function addKey($type, $options){
			$this->keys[] = new MySqlKey($type, $options);
		}
		public function addOption($value){
			$this->option .= $value;
		}
		public function get_add_column_sql($name, $type, $options){
			$sql = "ALTER TABLE $this->name ADD COLUMN ";
			$column = new MySqlColumn($name, $type, $options);
			$sql .= $column->getSql();
			return $sql;
		}
		public function get_alter_column_sql($name, $type, $options){
			$sql = "ALTER TABLE $this->name CHANGE $name ";
			$column = new MySqlColumn($name, $type, $options);
			$sql .= $column->getSql();
			return $sql;
		}
		public function get_rename_column_sql($old_name, $new_name, $type, $options){
			$sql = "ALTER TABLE $this->name CHANGE $old_name $new_name ";
			$column = new MySqlColumn($name, $type, $options);
			$sql .= $column->getSql();
			return $sql;
		}
		/* Example create statement:
		CREATE TABLE products (
			id INT(11) NOT NULL AUTO_INCREMENT,
			name VARCHAR(255) NOT NULL,
			modified timestamp,
			created DATETIME null,
			PRIMARY KEY (id)
		)
		ENGINE=MyISAM DEFAULT CHARSET=utf8;
		*/
		public function getSql(){
			$sql = 'CREATE TABLE ' . $this->name . '(';
			$sql .= $this->getColumnList();
			$sql .= (count($this->keys) > 0 ? ', ' . $this->getKeyList() : '');
			$sql .= ') ' . $this->option;
			return $sql;
		}
		
		private function getColumnList(){
			$list = array();
			foreach($this->columns as $key=>$column){
				$list[] = $column->getSql();
			}
			return implode(', ', $list);	
		}
		
		private function getKeyList(){
			$list = array();
			foreach($this->keys as $key){
				$list[] = $key->getSql();
			}
			return implode(', ', $list);
		}
	}
?>