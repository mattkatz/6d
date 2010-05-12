<?php
	class_exists('TestTemplate') || require('lib/TestTemplate.php');
	class_exists('FrontController') || require('lib/FrontController.php');
	class_exists('PostResource') || require('resources/PostResource.php');
	class_exists('FollowerResource') || require('resources/FollowerResource.php');
    class FrontControllerTest extends TestTemplate {
        public function __construct() {
            parent::__construct();
			$this->title = 'FrontController Tests';

        }
		public function __destruct(){}
				
		public function setUp(){}
		public function tearDown(){}
		
    }
?>
