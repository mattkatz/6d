<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('PostResource') || require('resources/PostResource.php');
	class_exists('LoginResource') || require('resources/LoginResource.php');
    class PostTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'PostResource Tests';
        }
		public function __destruct(){}
				
		public function setUp(){}
		public function tearDown(){}
		
    }
?>
