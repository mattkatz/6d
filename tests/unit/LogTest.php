<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('Log') || require('lib/Log.php');
    class LogTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'Log Tests';

        }
		public function __destruct(){}
				
		public function setUp(){}
		public function tearDown(){}
		
		public function testWriting(){
			$segments = explode('/', __FILE__);
			array_pop($segments);
			array_pop($segments);
			$path_to_logs = implode('/', $segments) . '/logs/';
			$log = new Log($path_to_logs, 5, false);
			try{
				$log->write('test');				
			}catch(Exception $e){
				error_log($e);
			}
			$this->assert(file_exists($path_to_logs . date("Ymd") . ".txt"), 'Testing writing to the log');
		}
    }
?>
