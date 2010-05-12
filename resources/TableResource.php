<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
class_exists('UserResource') || require('UserResource.php');
class TableResource extends AppResource{
	public function __construct($attributes = null){
		if(! AuthController::isAuthorized()){
			throw new Exception(FrontController::UNAUTHORIZED, 401);
		}
		parent::__construct($attributes);
		$this->db = Factory::get($this->config->db_type, $this->config);
		$this->host = $this->config->host;
	}
	public function __destruct(){
		parent::__destruct();
	}
	private $db;
	public $databases;
	public $tables;
	public $field_name;
	public $db_name;
	public $host;

	public function delete($db_name, $table_name){
		if($table_name != 'users'){
			$this->db->useDatabase($db_name);
			$this->db->deleteTable($table_name);
		}else{
			self::setUserMessage("Can't delete a users table.");
		}
		$this->tables = $this->db->getTables($db_name);
		$this->db_name= $db_name;
		$this->field_name = "Tables_in_$db_name";
		$this->output = $this->renderView('db/tables', null);
		return $this->renderView(null);
	}
	
}

?>