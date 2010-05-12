<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('Log') || require('lib/Log.php');
    class LogTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'Log Tests';

        }
		public function __destruct(){}
		public $path;
		
		public function setUp(){}
		public function tearDown(){}
		
		public function testWriting(){
			$segments = explode('/', str_replace('tests/', '', __FILE__));
			array_pop($segments);
			array_pop($segments);
			$this->path = implode('/', $segments) . '/logs/';
			$log = new Log($this->path, 5, false);
			$message = '';
			try{
				$log->write('test');				
			}catch(Exception $e){
				$message = $e;
			}
			$this->assert(file_exists($this->path . date("Ymd") . ".txt"), 'Testing writing to the log.' . $message);
			$log->delete();
		}
    }
?>
