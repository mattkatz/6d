<?php
	class_exists('Object') || require('lib/Object.php');
	abstract class MySqlRelationship extends Object{
		public function __construct($args){
			parent::__construct($args);
		}
		public function __destruct(){
			parent::__destruct();
		}
		
		public $through;
		public $withWhom;
		public $columnNames;
		
		public abstract function joinStatement($obj);
		public function selectList($obj){
			$objectColumnNames = array();
			$names = array();
			$objectReflector = new ReflectionClass(get_class($obj));
			$objectProperties = $objectReflector->getProperties();
			$objectProperties = array_filter($objectProperties, array($this, 'isPublicProperty'));
			$reflector = null;
			$through_id = (is_array($this->through) ? $this->through[0] : $this->through);
			$as_label = null;
			if($this->columnNames == null){
				$this->columnNames = array();
				$tableName = $this->withWhom->getTableName();
				$reflector = new ReflectionClass(get_class($this->withWhom));
				foreach($reflector->getProperties() as $property){
					if($property->isPublic() && $property->getName() != $through_id && !$objectReflector->hasProperty($property->getName()) && !is_object($this->withWhom->{$property->getName()}) && !is_array($this->withWhom->{$property->getName()})){
						if(method_exists($obj, 'willAddToSelectList')){
							$as_label = $obj->willAddToSelectList($property->getName());
						}
						$as_label = sprintf("%s.%s", $tableName, $property->getName());
						$this->columnNames[] = $as_label;
					}
					$as_label = null;
				}
				
			}
			return $this->columnNames;
		}
		
		public function isPublicProperty($property){
			return $property->isPublic();
		}
	}
?>