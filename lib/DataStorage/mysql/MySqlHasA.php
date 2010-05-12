<?php
	class_exists('MySqlRelationship') || require('MySqlRelationship.php');
	class MySqlHasA extends MySqlRelationship{
		public function __construct($args){
			parent::__construct($args);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public function joinStatement($obj){
			$joinToTableName = $obj->getTableName();
			if(method_exists($this->withWhom, 'getTableName')){
				$tableName = $this->withWhom->getTableName();
			}else{
				throw new Exception("Cannot create a join statement if the object doesn't specify it's table name by implementing getTableName().");
			}
			
			return sprintf("inner join %s on %s.%s=%s.%s", $tableName, $tableName, (is_array($this->through) ? $this->through[1] : $this->through), $joinToTableName, (is_array($this->through) ? $this->through[0] : $this->through));
		}
	}
?>