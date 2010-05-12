<?php
class_exists('AppResource') || require('AppResource.php');
class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
class_exists('UserResource') || require('UserResource.php');
class DbResource extends AppResource{
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
	public function get(){
		$this->databases = $this->db->getDatabases();
		$this->title = $this->config->database;
		$this->output = $this->renderView('db/index', null);
		return $this->renderView('layouts/db', null);
	}
	
	public function get_db_tables($db_name){
		$this->tables = $this->db->getTables($db_name);
		$this->db_name= $db_name;
		$this->field_name = "Tables_in_$db_name";
		$this->output = $this->renderView('db/tables', null);
		return $this->renderView(null);
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

	protected function createFirstVersion(){
		$factory = new DatabaseFactory();			
		$db = $factory->get(Config::getInstance()->getDatabaseProvider(), $this->connectionArgs, Config::getInstance()->getLogPath());
		if(!$db->exists(Config::getInstance()->getDatabase()))
			$did_create = $db->createDatabase(Config::getInstance()->getDatabase());

	}
	
}

?>