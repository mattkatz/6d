<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
class_exists('UserResource') || require('UserResource.php');
class TablesResource extends AppResource{
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
	
	public function get($db_name){
		$this->tables = $this->db->getTables($db_name);
		$this->db_name= $db_name;
		$this->field_name = "Tables_in_$db_name";
		$this->output = $this->renderView('db/tables', null);
		return $this->renderView(null);
	}
	
	public function showColumnsFor($db_name, $table_name){
		$col = $this->db->getColumns($db_name, $table_name);
		$this->view->setColumns($col);
		$this->view->setDb_name($db_name);
		$this->view->setTable_name($table_name);
		$this->view->addFileWithTheme('database/columns');
		return $this->view->render();
	}
	public function show($db_name){
		if($db_name == null)
			$this->db_name = $this->connectionArgs['databaseName'];
		try{
			$this->tables = $this->db->getTables($this->db_name);
		}catch(Exception $e){
			error_log($e->getMessage());
		}
		$this->title = "I'm mr. happy.";
		$this->output = $this->renderView('db/tables', null);
		return $this->renderView('layouts/db', null);
		
	}
	public function create($db_name){
		$this->db->createDatabase($db_name);
		$this->redirectTo('db');
	}

}

?>