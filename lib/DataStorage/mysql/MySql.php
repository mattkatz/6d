<?php
	class_exists('Security') || require('lib/DataStorage/Security.php');
	class_exists('MySqlTable') || require('lib/DataStorage/mysql/MySqlTable.php');
	class_exists('FindCommand') || require('lib/DataStorage/FindCommand.php');
	class_exists('ById') || require('lib/DataStorage/ById.php');
	class_exists('DSException') || require('lib/DataStorage/DSException.php');
	class_exists('String') || require('lib/String.php');
	class_exists('MySqlHasA') || require('MySqlHasA.php');
	class_exists('MySqlHasMany') || require('MySqlHasMany.php');
	class MySql{
		private $_config;
		private $_connectionId;
		private $_queryId;
		private $_relationships;
		private $_cachedSql;
		private $excluded_method_names;
		
		public $errorNumber;
		public $errorMessage;
		private $security;
		public function __construct($config){
			$this->_config = $config;
			$this->security = new Security();
			$this->excluded_method_names = array('getTableName', 'getPrimaryKey');
		}
		public function __destruct(){}
		
		public function sql(){
			return $this->_cachedSql;
		}
		public function delete(FindCommand $command = null, $obj){
			$sql = '';
			$key = 'id';
			if(method_exists($obj, 'getPrimaryKey')){
				$key = $obj->getPrimaryKey();				
			}
			// delete all children specified in it's relationship collection.
			/*if(method_exists($obj, 'getRelationships')){
				foreach($obj->getRelationships as $relationship){
					switch(get_class($relationship)){
						case('HasMany'):
							$sql = $this->constructDelete(new ByClause("$relationship->parentId=" . $obj->getAttribute($key)), $relationship->child);
							$this->execute($sql);
							break;
					}
				}
			}*/
			
			if($obj->{$key} != null){
				$sql = $this->constructDelete($command, $obj);
			}elseif($command != null){
				$sql = $this->constructDelete($command, $obj);
			}else
				return 0;
			$this->execute($sql);
			$affected_rows = $this->getAffectedRows();
			//$this->disconnect(null);
			return $affected_rows;
		}

		public function save(FindCommand $findType = null, $obj){
			// First determine if the id is set, if it is, then update, if not then insert.
			$sql = '';
			$key = 'id';
			if(method_exists($obj, 'getPrimaryKey')){
				$key = $obj->getPrimaryKey();				
			}
			
			$id = $obj->{$key};
			$id = ($id !== null && strlen($id) === 0) ? null : $id;
			if($id === null){
				$sql = $this->constructInsert($obj);
				$this->execute($sql);
				$obj->{$key} = $this->getInsertedId();
			}else{
				$sql = $this->constructUpdate($findType, $obj);
				$this->execute($sql);
			}
			return $obj;
		}
		private function getjoins($obj, $relationships){
			$joins = array();
			if(isset($relationships)){
				$joins = array();
				foreach($relationships as $relationship){
					switch(get_class($relationship)){
						case('HasA'):
							$r = new MySqlHasA(array('withWhom'=>$relationship->withWhom, 'through'=>$relationship->through));
							$joins[] = $r->joinStatement($obj);
							break;
						case('HasMany'):
							$r = new MySqlHasMany(array('withWhom'=>$relationship->withWhom, 'through'=>$relationship->through));
							$joins[] = $r->joinStatement($obj);
							break;
					}
				}
			}
			return implode(' ', $joins);
		}
		public function find(FindCommand $command, $obj){
			$sql = '';
			$records = null;
			$securedSql = $this->security->find($command, $obj);
			$object_to_populate = $obj;
			$key = 'id';
			$tableName = String::pluralize(String::decamelize(get_class($obj)));
			if(method_exists($obj, 'getPrimaryKey')){
				$key = $obj->getPrimaryKey();
			}
			
			if(method_exists($obj, 'getTableName')){
				$tableName = $obj->getTableName();
			}
			
			switch(get_class($command)){
				case('BySql'):
					$sql = $command->sql;
					break;
				case('ByClause'):
					$sql = $this->getSelectList($obj, $command->relationships);
					$sql .= ' from ' . $tableName;
					if($command->relationships != null){
						$sql .= ' ' . $this->getJoins($obj, $command->relationships);
					}
					if($command->clause != null){
						$sql .= '
where ' . $command->clause . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
					}elseif($securedSql != null){
						$sql .= '
where ' . $securedSql;
					}
					break;
				case('ByIds'):
					if($command->ids != null && is_array($command->ids)){
						$sql = $this->getSelectList($obj, $command->relationships);
						$sql .= ' from ' . $tableName;
						$sql .= ' ' . $this->getJoins($obj, $command->relationships);
					}else{
						throw new Exception('Ids are null or not an array.');
					}
					$sql .= '
where ' . $key . ' in (' . implode(', ', $this->sanitize($command->ids)) . ')' . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
					break;
				case('ByAttribute'):
					$sql = $this->getSelectList($obj, $command->relationships);
					$sql .= ' from ' . $tableName;
					$sql .= ' ' . $this->getJoins($obj, $command->relationships);
					if(isset($command->value)){
						if(is_array($command->value)){
							if(ctype_digit($command->value[0])){
								$sql .= '
where ' . $command->name . ' in (' . implode(', ', $this->sanitize($command->value)) . ')' . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
							}else{
								$sql .= '
where ' . $command->name . ' in (\'' . implode('\', \'', $this->sanitize($command->value)) . '\')' . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
							}
						}else{
							$sql .= '
where ' . $command->name . '=\'' . $this->sanitize($command->value) . '\'';
						}
					}else{
						throw new Exception('ByAttribute:value is null.');
					}
					break;
				case('ById'):				
					$sql = $this->getSelectList($obj, $command->relationships);
					$sql .= ' from ' . $tableName;
					$sql .= ' ' . $this->getJoins($obj, $command->relationships);
					if($command->id == null)
						$sql .= '
where ' . $key . '=' . $this->typeIt($obj->{$obj->primaryKey}) . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
					else
						$sql .= '
where ' . $key . '=' . $this->typeIt($command->id) . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
					break;
				case('All'):
					if($command->sql != null){
						$sql .= $command->sql . (strlen($securedSql) > 0 ? ' and ' . $securedSql : '');
					}else{
						$sql = $this->getSelectList($obj, $command->relationships);
						$sql .= ' from ' . $tableName;
						$sql .= ' ' . $this->getJoins($obj, $command->relationships);
						$sql .= (strlen($securedSql) > 0) ? '
where ' . $securedSql : '';
					}
					break;
				default:
					throw new Exception('Invalid find type.');
					break;
			}
			//$object_to_populate = $this->getObjectToPopulate($command->relationships, $object_to_populate);
			$sql .= $this->addOrderBy($command);
			$sql .= $this->addLimit($command->limit);
			$this->execute($sql);
			//$records = $this->getRecords($object_to_populate);
			$records = $this->populate($object_to_populate, $command);
			if(count($records) > 0){
				return (count($records) == 1 && $command->limit == 1) ? $records[0] : $records;
			}else{
				return null;
			}
		}
		private function populate($obj, $command){
			$records = array();
			$attributes = array();
			$className = get_class($obj);
					
			if($this->_queryId){
				while($row = mysql_fetch_object($this->_queryId)){
					// Populate the object hiearchy with the result columns.
					$attributes = get_object_vars($row);
					$non_existing_attr = array();
					$model = new $className(null);
					$r = new ReflectionClass($className);
					foreach($attributes as $key=>$value){
						if(!is_object($model->{$key})){
							$value = $this->castIt($row->$key);
							$model->$key = $value;
						}
					}
					
					if($command->relationships != null && count($command->relationships) > 0){
						foreach($command->relationships as $relationship){
							$child_name = get_class($relationship->withWhom);
							$child_name = strtolower(String::decamelize($child_name));
							if($r->hasProperty($child_name)){
								$property = $r->getProperty($child_name);
								$child = $this->populateObjectWithRow($property->getValue($model), $row);
								$model->{$child_name} = $child;
							}
						}
					}
					$records[] = $model;
					$model = null;
				}
			}else{
				throw new Exception('MySql error: ' . $this->errorNumber . '=' . $this->errorMessage);
			}
			return $records;			
		}
		private function populateWithRow($obj, $row){
			$attributes = get_object_vars($row);
			$r = new ReflectionClass(get_class($obj));
			foreach($attributes as $attribute=>$value){
				if($r->hasProperty($attribute)){
					$property = $r->getProperty($attribute);
					$value = $this->castIt($value);
					$obj->{$attribute} = $value;
				}
			}
			return $obj;			
		}
		private function getObjectToPopulate($relationship, $object_to_populate){
			if($relationship != null){
				$object_to_populate = $relationship->withWhom;
			}
			return $object_to_populate;
		}
		private function addOrderBy($command){
			$sql = null;
			$order_by = array();
			if($command->order_by != null){
				foreach($command->order_by as $column_name=>$direction){
					$order_by[] = $column_name . ($direction == 'desc' ? ' desc' : ' asc');
				}
				$sql .= ' order by ' . implode(', ', $order_by);
			}
			return $sql;
		}
		private function addLimit($limit = 0){
			if(!is_array($limit)){
				if($limit > 0){
					return ' limit ' . $limit;
				}else{
					return null;
				}
			}else{
				return " limit $limit[0], $limit[1]";
			}
		}
		private function getSelectList($obj, $relationships){
			$sql = 'select ';
			$sql .= $obj->getTableName() . '.*';
			if(method_exists($obj, 'didCreateSelectList')){
				$sql = $obj->didCreateSelectList($sql);
			}
			$mysqlRelationship = null;
			if($relationships != null){
				foreach($relationships as $relationship){
					switch(get_class($relationship)){
						case('HasA'):
							$mysqlRelationship = new MySqlHasA(array('withWhom'=>$relationship->withWhom, 'through'=>$relationship->through));
							$list[] = $mysqlRelationship->selectList($obj);
							break;
						case('HasMany'):
							$mysqlRelationship = new MySqlHasMany(array('withWhom'=>$relationship->withWhom, 'through'=>$relationship->through));
							$list[] = $mysqlRelationship->selectList($obj);
							break;
					}
					$mysqlRelationship = null;
				}
				if(count($list)>0){
					$sql .= ', ';
				}
				
				$each_one = array();
				foreach($list as $columns){
					$each_one[] = implode(', ', $columns);
				}
				if(count($each_one) > 0){
					$sql .= implode(', ', $each_one);
				}
			}
			return $sql;
		}
		public function findBySql($sql){
			$this->execute($sql);
		}
		public function useDatabase($db){
			$this->_config->database = $db;
			if($db != null && $this->_connectionId != null){
				try{
					mysql_select_db($this->_config->database, $this->_connectionId);
					$this->setError(null);
					if($this->errorNumber > 0){
						throw new Exception("There was a problem using database named '{$this->_config->database}'. {$this->errorNumber}: {$this->errorMessage}
						", $this->errorNumber);		
					}
				}catch(Exception $e){
					throw $e;
				}
			}
		}
		
		public function createDatabase($databaseName){
			if(!$this->exists($databaseName)){
				$sql = "CREATE DATABASE $databaseName";
				$this->execute($sql);
				//$this->disconnect(null);
				return true;
			}
			return false;
		}
		
		public function install($table){
			if(!$this->tableExists($table)){
				$sql = <<<eos
CREATE TABLE {$table->name} (
%s
) %s
eos;
				$columnBuilder = array();
				foreach($table->columns as $column){
					$columnBuilder[] = "{$column->name} {$column->type}{$column->getSize()}{$column->getIsNullable()}{$column->getDefault()} {$column->getOptions()}";
				}

				if($table->keys != null && count($table->keys) > 0){
					if(array_key_exists('PRIMARY KEY', $table->keys)){
						if(is_array($table->keys['PRIMARY KEY'])){
							$columnBuilder[] = 'PRIMARY KEY (' . implode($table->keys['PRIMARY KEY'], ", ") . ')';
						}else{
							$columnBuilder[] = "PRIMARY KEY ({$table->keys['PRIMARY KEY']}";
						}
					}

					if(array_key_exists('KEY', $table->keys)){
						$columnBuilder[] = 'KEY ' . $table->keys['KEY'][0] . '(' . $table->keys['KEY'][1] . ')';
					}
				}

				$sql = sprintf($sql, implode(', ', $columnBuilder) . ')', ($table->options != null) ? implode(' ', $table->options) : '');
				$this->execute($sql);
				//$this->disconnect(null);
			}
		}
		public function count($obj){
			$sql = sprintf('select count(' . $obj->primaryKey .') as NumberOfRecords from ' . $obj->tableName);
			$this->execute($sql);
			$numberOfRecords = 0;
			if($this->_queryId){
				while($row = mysql_fetch_object($this->_queryId)){
					$numberOfRecords = $row->NumberOfRecords;
				}
				//$this->disconnect(null);
			}else{
				throw new Exception('MySql error: ' . $this->errorNumber . '=' . $this->errorMessage);
			}
			return $numberOfRecords;
		}
		public function deleteDatabase($databaseName){
			if($this->exists($databaseName) && !in_array($databaseName, array('information_schema', 'mysql'))){
				$sql = "DROP SCHEMA $databaseName";
				$this->execute($sql);
				//$this->disconnect(null);
				return true;
			}
			return false;
		}
		public function columnExists($tableName, $columnName){
			$columns = $this->getColumns($tableName);			
			foreach($columns as $column){
				if($column->Field == $columnName)
					return true;
			}
			return false;
		}
		
		public function testConnection(){
			$this->connect(null);				
		}
		
		public function tableExists($name){
			$sql = 'SHOW TABLES';
			$this->execute($sql);
			$rows = $this->getRows();
			if($rows != null){
				foreach($rows as $table){
					if($table->{'Tables_in_'.$this->_config->database} == $name){
						return true;
					}
				}
			}
			return false;
		}
		public function exists($name){
			$test = 'SHOW DATABASES';
			$this->execute($test);
			$rows = $this->getRows();
			foreach($rows as $row=>$database){
				if($database->Database == $name){
					return true;
				}
			}
			return false;
		}
		public function deleteTable($tableName){
			$sql = 'DROP TABLE ' . $tableName;
			if($this->tableExists($tableName)){	
				$this->execute($sql);
				//$this->disconnect(null);
				return true;
			}
			return false;
		}
		private function constructDelete(FindCommand $findType = null, $obj){
			$sql = "DELETE FROM {$obj->getTableName()}";
			$key = 'id';
			if(method_exists($obj, 'getPrimaryKey')){
				$key = $obj->getPrimaryKey();
			}
			$securedSql = $this->security->delete($obj);
			switch(get_class($findType)){
				case('ById'):
					$sql .= " WHERE {$key} = {$findType->id}";
					break;
				case('ByIds'):
					$sql .= " WHERE {$key} in(" . implode(', ', $findType->ids) . ")";
					break;
				case('ByClause'):
					$sql .= ' WHERE ' . $findType->clause;
					break;
				case('All'):
					throw new Exception('All not implemented for delete.');
					break;
				default:
					$sql .= " WHERE {$key} = " . $this->typeIt($obj->{$key});
					break;
			}
			$sql .= (strlen($securedSql) > 0) ? ' ' . $securedSql : '';
			return $sql;
		}
		private function constructInsert($obj){
			$value = null;
			$tableName = $obj->getTableName();
			$reflector = new ReflectionClass(get_class($obj));
			$format = <<<eos
INSERT INTO {$tableName} (%s)
values (%s)
eos;
			$sql = '';
			$columns = array();
			$values = array();
			$key = 'id';
			$should_insert_id = false;
			$should_ad = false;
			if(is_object($obj)){
				if(method_exists($obj, 'getPrimaryKey')){
					$key = $obj->getPrimaryKey();
				}
				if(method_exists($obj, 'shouldInsertId')){
					$should_insert_id = $obj->shouldInsertId();
				}
				$methods = $reflector->getMethods();
				
				foreach($methods as $method){
					$method_name = $method->getName();
					if(!array_search($method_name, $this->excluded_method_names) && $method->isPublic() && strpos($method_name, 'get') === 0){
						$name = str_replace('get', '', $method_name);
						$lower_case_name = strtolower($name);
						if($reflector->hasMethod('set' . $name)){
							$value = $method->invoke($obj);
							if($lower_case_name === $key){
								$should_ad = $should_insert_id;
							}else{
								$should_ad = (!is_object($value) && !is_array($value));
							}
							if($should_ad){
								$columns[] = $lower_case_name;

								if(method_exists($obj, 'willAddFieldToSaveList')){
									$value = $obj->willAddFieldToSaveList($lower_case_name, $value);
								}
								// 2009-09-23, jguerra: I put this here instead of above the willAdd... check because 
								// it needs to type the value after the observer has a chance to modify it.
								$values[] = $this->typeIt($value);
								if(method_exists($obj, 'didAddFieldToSaveList')){
									$obj->didAddFieldToSaveList($lower_case_name, $value);
								}
							}
						}
						$name = null;
						$should_ad = false;
					}
				}
			}
			
			return sprintf($format, implode(', ', $columns), implode(', ', $values));
		}
		private function constructUpdate(FindCommand $findType = null, $obj){
			$id = null;
			$value = null;
			$name = '';
			$tableName = $obj->getTableName();
			$reflector = new ReflectionClass(get_class($obj));
			$key = 'id';
			if(method_exists($obj, 'getPrimaryKey')){
				$key = $obj->getPrimaryKey();
			}
			$format = <<<eos
UPDATE {$tableName} SET %s
eos;

			if($findType != null){
				switch(get_class($findType)){
					case('Clause'):
						$format .= " WHERE $findType->clause";
						break;
				}
			}else{
				$format .= " WHERE " . $key . " = %s";
			}

			$sql = '';
			$columns = array();
			$values = array();
			$getter = null;
			$key = 'id';
			if(is_object($obj)){
				if(method_exists($obj, 'getPrimaryKey')){
					$key = $obj->getPrimaryKey();
				}
				
				$id = $obj->{'get' . strtoupper($key)}();
				$methods = $reflector->getMethods();
				
				foreach($methods as $method){
					$method_name = $method->getName();
					if(!array_search($method_name, $this->excluded_method_names) && $method->isPublic() && strpos($method_name, 'get') === 0){
						$property_name = strtolower(str_replace('get', '', $method_name));
						if($reflector->hasMethod('set' . $property_name)){
							$value = $method->invoke($obj);
							// 2009-08-31, jguerra: I had a check for null included but boolean attributes were not 
							// getting saved when unchecked from a form. So I removed the null check and that fixed
							// the issue. Now I can uncheck a checkbox on a form for a boolean attribute and it gets
							// saved as false.
							if($property_name != $key
								&& !is_object($value)
								&& !is_array($value)){

								$value = $this->typeIt($value);
								if(method_exists($obj, 'willAddFieldToUpdateList')){
									$value = $obj->willAddFieldToUpdateList($property_name, $value);
								}
								$values[] = $property_name . '=' . $value;
								if(method_exists($obj, 'didAddFieldToUpdateList')){
									$obj->willAddFieldToUpdateList($property_name, $value);
								}

							}

						}
					}
				}
			}
			return sprintf($format, implode(', ', $values), $this->typeIt($id));
		}
		private function castIt($value){
			if(is_bool($value)){
				return (boolean)$value;
			}
			else if(is_null($value))
				return null;
			else if(is_float($value))
				return floatval($value);
			else if(is_int($value))
				return intval($value);
			else if(is_object($value))
				return $value;
			else if(is_array($value))
				return $value;
			else if(is_string($value)){
				if(is_numeric($value))
					return $value;
				else
					return urldecode($value);
			}
			else
				return $value;
		}
		private function typeIt($value){
			$is_date = (strtotime($value) !== false);
			if(is_bool($value)){
				return ($value) ? 1 : 0;
			}
			else if(is_null($value))
				return 'NULL';
			else if(is_float($value))
				return $value;
			else if(is_int($value))
				return $value;
			else if(is_object($value))
				return $value;
			else if(is_array($value))
				return $value;
			else if($is_date)
				return sprintf("'%s'", $value);
			else if(is_string($value)){
				if(is_numeric($value))
					return $value;
				else
					return sprintf("'%s'", urlencode($value));
			}
			else
				return $value;
		}

		public function getColumns($table_name){
			$this->connect(null);
			$sql = "show columns from $table_name";
			$this->execute($sql);
			$records = $this->getRows();
			if(count($records) > 0){
				return (count($records) == 1) ? $records[0] : $records;
			}else{return null;}
		}
		public function getDatabases(){
			$this->connect(null);
			$sql = 'show databases';
			$this->execute($sql);
			$records = $this->getRows();
			if(count($records) > 0){
				return (count($records) == 1) ? $records[0] : $records;
			}else{return null;}
		}
		public function getTables($db_name){
			$this->connect(null);
			$sql = "show tables from $db_name";
			$this->execute($sql);
			$records = $this->getRows();
			if(count($records) > 0){
				return $records;
			}else{return array();}
		}
		private function connect($config = null){
			if($config != null)
				$this->_config = $config;
			if($this->_config == null){
				throw new Exception('Connection object is not set.');
			}
			if($this->_connectionId == null){
				$this->_connectionId = mysql_connect($this->_config->host, $this->_config->user_name, $this->_config->password);
			}

			if($this->_connectionId == false){
				$this->setError(null);
				throw new DSException(new Exception('The connection to the MySql server failed. Please check your user name and password again and that the database has been created. MYSQL ERROR: ' . $this->errorMessage, 0));
				
			}

			try{
				if($this->_config->database != null){
					$this->useDatabase($this->_config->database);
				}
			}catch(Exception $e){
				$this->setError($e);
				throw new DSException(new Exception('Database does not exist.', 0));
			}
			
		}
		
		private function setError($e){
			$this->errorNumber = mysql_errno();
			$this->errorMessage = mysql_error() . $e;
		}
		public function disconnect($queryId = null){
			if($queryId != null)
				mysql_free_result($queryId);
			elseif($this->_queryId != null)
				mysql_free_result($this->_queryId);
		}
		public function getRows(){
			$rows = null;
			if($this->_queryId){
				while($row = mysql_fetch_object($this->_queryId)){
					$rows[] = $row;
				}
				//$this->disconnect(null);
			}else{
				throw new Exception('MySql error: ' . $this->errorNumber . '=' . $this->errorMessage);
			}
			return $rows;
		}
		private function populateObjectWithRow($obj, $row){
			$attributes = get_object_vars($row);
			foreach($attributes as $attribute=>$value){				
				$value = $this->castIt($value);
				$obj->{$attribute} = $value;
			}
			return $obj;
		}
		private function getRecords(Object $object_to_populate){
			$records = array();
			$attributes = array();
			$className = get_class($object_to_populate);
					
			if($this->_queryId){
				while($row = mysql_fetch_object($this->_queryId)){
					// Populate the object hiearchy with the result columns.
					$attributes = get_object_vars($row);
					$model = new $className(null);
					
					$r = new ReflectionClass($className);
					$properties = $r->getProperties();
					foreach($properties as $property){
						$name = $property->getName();
						if($property->isPublic()){
							if(!is_object($model->{$name})){
								$value = $this->castIt($row->$name);
								$model->{$name} = $value;
							}
						}
					}
					$records[] = $model;
					$model = null;
				}
			}else{
				throw new Exception('MySql error: ' . $this->errorNumber . '=' . $this->errorMessage);
			}
			return $records;
		}
		private function getInsertedId(){
			return mysql_insert_id($this->_connectionId);
		}
		private function getAffectedRows(){
			return mysql_affected_rows($this->_connectionId);
		}
		public function execute($sql){
			$this->connect($this->_config);
			$this->_cachedSql = $sql;
			$this->_queryId = mysql_query($sql, $this->_connectionId);
			$this->setError(null);
			if($this->errorNumber > 0){
				error_log('MySql execute error: ' . $this->errorNumber . ' ' . $this->errorMessage);
				if($this->errorNumber == 1046){
					throw new DSException(new Exception($this->errorMessage, $this->errorNumber));
				}else{
					throw new Exception($this->errorMessage . '>>> ' . $sql, $this->errorNumber);					
				}
			}
		}
		private function sanitize($sql){			
			$sql = str_ireplace("'", "\'", $sql);
			$sql = str_ireplace(";", "\;", $sql);
			return $sql;
		}
		private function unsanitize($text){
			$text = str_ireplace("\'", "'", $text);
			$text = str_ireplace("\;", ";", $text);
			return $text;
		}
		public function getTable($name){
			return new MySqlTable($name);
		}
	}
?>
