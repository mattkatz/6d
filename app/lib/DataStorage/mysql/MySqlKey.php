<?php
	class MySqlKey{
		public $type;
		public $options;
		public function __construct($type, $options){
			$this->type = $type;
			$this->options = $options;
		}
		public function __destruct(){}
		public function getSql(){
			$sql = '';
			switch($this->type){
				case('key'):
					$sql .= ' KEY ' . $this->getKeyNameValue();
					break;
				case('primary'):
					$sql .= ' PRIMARY KEY (' . $this->getPrimaryKeyColumns() . ')';
					break;
				case('unique'):
					$sql .= ' UNIQUE KEY ' . $this->getKeyNameValue();
					break;
				default:
					throw new Exception("$this->type is not implemented.");
					break;
			}
			return $sql;
		}
		private function getKeyNameValue(){
			$sql = '';
			foreach($this->options as $key=>$value){
				if(is_array($value)){
					$sql = $key . '('. implode(', ', $value) . ')';
				}else{
					$sql = $key . '('. $value . ')';
				}
			}
			return $sql;
		}
		private function getPrimaryKeyColumns(){
			if(is_array($this->options)){
				return implode(', ', $this->options);
			}else{
				return $this->options;
			}
		}
	}
?>