<?php
	class MySqlColumn{
		public $name;
		public $type;
		public $options;
		public function __construct($name, $type, $options){
			$this->name = $name;
			$this->type = $type;
			$this->options = $options == null ? array() : $options;
		}
		public function __destruct(){}
		public function getSql(){
			$sql = $this->name;
			switch($this->type){
				case('tinyinteger'):
					$sql .= ' INT(4)';
					break;
				case('integer'):
					$sql .= ' INT(11)';
					break;
				case('biginteger'):
					$sql .= ' BIGINT(20)';
					break;
				case('string'):
					$sql .= ' VARCHAR(' . (array_key_exists('size', $this->options) ? $this->options['size'] : 255) . ')';
					break;
				case('double'):
					$sql .= ' DOUBLE' . (array_key_exists('size', $this->options) ? ' (' . $this->options['size'] . ')' : '');
					break;
				case('decimal'):
					$sql .= ' DECIMAL' . (array_key_exists('size', $this->options) ? ' (' . $this->options['size'] . ')' : '');
					break;
				case('text'):
						$sql .= ' TEXT' . (array_key_exists('size', $this->options) ? ' (' . $this->options['size'] . ')' : '');
					break;
				case('tinytext'):
						$sql .= ' TINYTEXT' . (array_key_exists('size', $this->options) ? ' (' . $this->options['size'] . ')' : '');
					break;
				case('boolean'):
					$sql .= ' TINYINT(1)';
					break;
				case('datetime'):
					$sql .= ' DATETIME';
					break;
				case('timestamp'):
					$sql .= ' TIMESTAMP';
					break;
				case('longtext'):
					$sql .= ' LONGTEXT';
					break;
				case('mediumtext'):
					$sql .= ' MEDIUMTEXT';
					break;
				case('binary'):
					$sql .= ' BLOB';
					break;
				case('smallbinary'):
					$sql .= ' TINYBLOB';
					break;
				default:
					throw new Exception("Data type '" . $this->type . "' hasn't been implemented.");
					break;
			}
			if(array_key_exists('is_nullable', $this->options)){
				$sql .= ' ' . ($this->options['is_nullable'] ? ' NULL ' : ' NOT NULL ');
			}
			if(array_key_exists('default', $this->options)){
				if($this->options['default'] === null)
					$sql .= ' DEFAULT NULL ';
				else if(is_numeric($this->options['default']))
					$sql .= ' DEFAULT ' . $this->options['default'] . ' ';
				else if(is_bool($this->options['default']))
					$sql .= ' DEFAULT ' . ($this->options['default'] ? '1' : '0') . ' ';
				else if(strstr($this->options['default'], '(') === false)
					$sql .= " DEFAULT '" . $this->options['default'] . "' ";
				else
					$sql .= ' DEFAULT ' . $this->options['default'] . ' ';
			}
			if(array_key_exists('auto_increment', $this->options)){
				$sql .= ($this->options['auto_increment'] ? ' AUTO_INCREMENT ' : '');
			}
			$sql = trim($sql);
			return $sql;
		}
	}
?>