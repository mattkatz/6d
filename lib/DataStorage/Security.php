<?php
	class_exists('FindCommand') || require('lib/DataStorage/FindCommand.php');
	interface ISecurity{
		public function find(FindCommand $command, $obj);
		
	}
	class Security implements ISecurity{
		public function __construct(){}
		public function __destruct(){}
		public function find(FindCommand $command, $obj){
			$class = get_class($obj);
			$clause = '';
			/*switch($class){
				case('User'):
					if(array_key_exists('user_id', $_SESSION) && $_SESSION["user_id"] > 0){
						$clause = '(' . (strlen(TABLE_PREFIX) > 0 ? TABLE_PREFIX . '.' : ''). $obj->tableName . '.id =' . $_SESSION["user_id"] . ' or (select 1 from ' . (strlen(TABLE_PREFIX) > 0 ? TABLE_PREFIX.'.' : '') . 'user_roles ur inner join ' . (strlen(TABLE_PREFIX) > 0 ? TABLE_PREFIX.'.' : ''). 'roles r on r.id = ur.role_id where ur.user_id = ' . $_SESSION["user_id"] . ' and r.name = \'admin\') = 1)';
					}
					break;
			}*/
			return $clause;
		}
		public function delete($obj){
			$class = get_class($obj);
			$clause = '';
			/*switch($class){
				case('Project'):
					$clause = '(select name from ' . (strlen(Config::instance()->tablePrefix) > 0 ? Config::instance()->tablePrefix . '.' : '') . 'users where id = ' . $_SESSION['user_id'] . ') <> \'demo\'';
					break;
			}*/
			return $clause;
		}
		
	}
?>