<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('DataStorage') || require('lib/DataStorage/DataStorage.php');
	
    class TableTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'Table Tests';

        }
		public function __destruct(){}
		
		public $db;
		
		public function setUp(){
			$config = new AppConfiguration();
			$config->database = 'wordpress_test';
			$this->db = Factory::get('MySql', $config);
		}
		public function tearDown(){
			$this->db->deleteTable('test_table');
		}
		
		public function testSave(){
			$message = '';
			$table = new Table('test_table', $this->db);
			$table->addColumn('id', 'biginteger', array('is_nullable'=>false, 'auto_increment'=>true));
			$table->addColumn('name', 'string', array('is_nullable'=>false, 'default'=>'', 'size'=>200));
			$table->addKey('primary', 'id');
			$table->addKey('key', array('name'=>'name'));
			$table->addOption('ENGINE=MyISAM DEFAULT CHARSET=utf8');
			$errors = $table->save();
			if(is_array($errors) && count($errors) > 0){
				foreach($errors as $key=>$error){
					$message .= sprintf('%d: %s<br />', $key, $error);
				}
			}
			
			$this->assert(count($errors) == 0, 'TableTest::testSave=>' . $message);
		
		
			$errors = $table->alter_column('name', 'integer', null);
			$column = null;
			if(is_array($errors) && count($errors) > 0){
				foreach($errors as $key=>$error){
					$message .= sprintf('%d: %s<br />', $key, $error);
				}
			}else{
				$column = $table->get_column('name');
			}
			
			$this->assert(count($errors) == 0, 'TableTest::testAlter_column=>' . $message);
			$this->assert(stripos($column->Type, 'int') !== false, 'TableTest::testAltelr_column=>Column type should be int now.');
		}
		
    }
?>
