<?php
interface_exists('IPlugin') || require('lib/interfaces/IPlugin.php');
class_exists('HTMLPurifier') || require('HTMLPurifier.php');
class PostPlugin_HTMLPurifier implements IPlugin{
	
	public function __construct(){}
	public function __destruct(){}
	
	public function execute($text){
		$config = HTMLPurifier_Config::createDefault();
		$config->set('Core', 'Encoding', 'ISO-8859-1');
		$config->set('HTML', 'TidyLevel', 'heavy' );
		$config->set('Cache', 'SerializerPath', '/var/cache/HTMLPurifier' );
		$purifier = new HTMLPurifier($config);
	  	return $purifier->purify($text);
	}
}