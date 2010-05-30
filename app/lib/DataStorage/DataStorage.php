<?php
	class_exists('Factory') || require('Factory.php');
	class_exists('All') || require('All.php');
	class_exists('ByAttribute') || require('ByAttribute.php');
	class_exists('ByClause') || require('ByClause.php');
	class_exists('ById') || require('ById.php');
	class_exists('ByIds') || require('ByIds.php');
	class_exists('BySql') || require('BySql.php');
	class_exists('DSException') || require('DSException.php');
	class_exists('FindCommand') || require('FindCommand.php');
	class_exists('Table') || require('Table.php');
	class DataStorage{
		public function __construct(){}
		public function __destruct(){}
	}
?>