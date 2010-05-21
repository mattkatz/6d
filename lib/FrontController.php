<?php
class_exists('String') || require('lib/String.php');
class_exists('Object') || require('lib/Object.php');
class FrontController extends Object{
	public function __construct($context){
		self::$start_time = microtime(true);
		$this->context = $context;
		$this->initSitePath();
	}
	public function __destruct(){}
	public static $start_time;
	public static $end_time;
	public $request_time;
	const UNAUTHORIZED = '401: Unauthorized';
	const NOTFOUND = '404: Not Found';
	public $context;
	public static $site_path;
	public static $error_html;
	public $delegate;

	
	public static function sendHeaders($headers){
		foreach($headers as $key=>$value){
			header(sprintf("%s: %s", $key, $value));
		}		
	}
	public static function sendXmlHeaders($type){
		$headers = array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT');

		if($type == null){
			$headers['Content-type'] = 'text/xml;charset=UTF-8';
		}else{
			$headers['Content-type'] = 'application/' . $type . '+xml;charset=UTF-8';
		}
		self::sendHeaders($headers);
	}
	public static function sendJsonpHeaders(){
		self::sendHeaders(array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT'
			, 'Content-type'=>'application/javascript;charset=UTF-8'));
	}
	public static function sendJavascriptHeaders(){
		self::sendHeaders(array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT'
			, 'Content-type'=>'text/javascript;charset=UTF-8'));
	}
	public static function sendJsonHeaders(){
		self::sendHeaders(array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT'
			, 'Content-type'=>'application/json;charset=UTF-8'));
	}
	public static function sendHtmlHeaders($length){
		self::sendHeaders(array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT'
			, 'Content-type'=>'text/html;charset=UTF-8'
			, 'Content-length'=> $length));
	}
	public static function send301Header($url){
		self::sendHeaders(array(
			'Cache-Control'=>'no-cache, must-revalidate'
			, 'Expires'=>'Mon, 04 Oct 2004 10:00:00 GMT'
			, 'Content-type'=>'text/html;charset=UTF-8'
			, 'Content-length'=> $length
			, 'Location'=>$url));
		header('HTTP/1.1 301 Moved Permanently');
		header("Location: $url");
	}
	public static function send401Headers($message, $realm){
		header('WWW-Authenticate: Digest realm="'.$realm.'",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
		header('HTTP/1.1 401 Unauthorized');
		die($message);
	}
	public static function send404Headers($message){
		header($_SERVER["SERVER_PROTOCOL"].' ' . self::NOTFOUND, true, 404);
		self::sendHeaders(array('Status'=>$message));
	}
	public static function sendHeadersForFileType($file_type, $length){
		switch($file_type){
			case('js'):
				self::sendJavascriptHeaders();
				break;
			case('jsonp'):
				self::sendJsonpHeaders();
				break;
			case('json'):
				self::sendJsonHeaders();
				break;
			case('xml'):
				self::sendXmlHeaders(null);
				break;
			case('rss'):
				self::sendXmlHeaders('rss');
				break;
			case('atom'):
				self::sendXmlHeaders('atom');
				break;
			default:
				self::sendHtmlHeaders($length);
				break;
		}
	}
	public static function themePath(){
		$config = null;
		if(class_exists('AppConfiguration')){
			$config = new AppConfiguration();
		}
		if($config != null){
			return 'themes/' . $config->getTheme();
		}else{
			return 'themes/default';
		}
	}
	public static function isSecure(){
		return array_key_exists('HTTPS', $_SERVER);
	}
	public static function canRewriteUrl(){
		return file_exists(self::getDocumentRoot() . self::getVirtualPath() . '/.htaccess');
	}
	public static function index_script(){
		return self::canRewriteUrl() ? null : 'index.php/';
	}
	public static function urlFor($resource = null, $params = null, $make_secure = false){
		$config = (class_exists('AppConfiguration') ? new AppConfiguration(null) : null);
        $use_clean_urls = self::canRewriteUrl();
        $query_string = null;
		$resource_id = null;
		if($make_secure && $config != null && strlen($config->ssl_path) > 0){
			$url = sprintf('https://%s/%s', $config->ssl_path, $resource);
		}else{
			$site_path = self::$site_path;
			if($make_secure){
				$site_path = str_replace('http:', 'https:', $site_path);
			}else{
				$site_path = str_replace('https:', 'http:', $site_path);
			}
		}

        if(in_array($resource, array('themes', 'js', 'css', 'images', 'media'))){
			$resource = ($resource === 'themes' ? self::themePath() . '/' : $resource . '/');
            $use_clean_urls = true;
			return $site_path . $resource;
		}
		
        if($params != null){
			$query_string = array();
			foreach($params as $key=>$val){
				if($key == 'id'){
					$resource_id = $val;
				}else{
					$query_string[] = sprintf('%s=%s', $key, $val);					
				}
			}
		}
        
		$url = '';
		if(!$use_clean_urls){
			$resource = self::index_script() . ($resource != null ? '' . $resource : null);
		}else{
	        $resource =  ($resource !== null ? $resource : null);
		}
		if($resource_id !== null){
			$resource .= '/' . $resource_id;
		}
        if($query_string != null){
			$resource .= '?';
            $resource .= implode('&', $query_string);
        }
		if($make_secure && $config != null && strlen($config->ssl_path) > 0){
			$url = sprintf('https://%s/%s', $config->ssl_path, $resource);
		}else{			
			$url = $site_path . $resource;
		}
		return $url;
	}
	private function initSitePath(){
        $is_secure = self::isSecure();
		$config = null;
		if(class_exists('AppConfiguration')){
			$config = new AppConfiguration();
		}
		if($config != null && strlen($config->site_path) > 0){
			self::$site_path = sprintf('%s://%s/', ($is_secure ? 'https' : 'http'), $config->site_path);
		}else{
			$virtual_path = self::getVirtualPath();
			self::$site_path = sprintf('%s://%s%s/', ($is_secure ? 'https' : 'http'), $_SERVER['SERVER_NAME'], ($virtual_path != null ? '/'.$virtual_path : null));
		}
	}
	
	// Assumes that the first part of the url is the resource name.
	public function parseForResourceName($default_value, $parts){
		$r = $default_value;
		// This logic just sets the resource from the url, assuming that the resource name is the first item
		// in the array.
		if(count($parts) > 0){
			$r = array_shift($parts);
		}
		if(stripos($r, '.') !== false){
			$extension = explode('.', $r);
			$r = $extension[0];
		}
		if(stripos($r, '?') !== false){
			$query_string = explode('?', $r);
			$r = $query_string[0];
		}
		if(stripos($r, '&') !== false){
			$query_string = explode('&', $r);
			$r = $query_string[0];			
		}
		return $r;
	}
	
	// This method assumes that the file type will be appended to the end of the resource like resource.html
	// It also restricts the file types to solve the problem where a uniqid (with a "." in it) is used as 
	// the resource id in the url like resource/444b23bsd22.12323 is used. If the file types are not restricted
	// then this uri will break, resulting in a page not found 404.
	public function parseForFileType($parts){
		$file_type = 'html';
		if($parts != null && count($parts) > 0){
			$last_item = $parts[count($parts) - 1];
			if(stripos($last_item, '.') !== false){
				$extension = explode('.', $last_item);
				$file_type = $extension[count($extension) - 1];
				// And for environments like Dreamhost, where I had to add a ? to the script path (index.php?/$1) 
				// in the htaccess file, I have to now do this because the $file_type might have ?parm=value
				// with it.
				if(stripos($file_type, '?') !== false){
					$file_type = explode('?', $file_type);
					$file_type = $file_type[0];
				}
				if(stripos($file_type, '&') !== false){
					$file_type = explode('&', $file_type);
					$file_type = $file_type[0];
				}

			}
		}
		if(!in_array($file_type, array('phtml', 'html', 'json', 'xml', 'js', 'atom', 'rss'))){
			$file_type = 'html';
		}
		return $file_type;
	}
	
	/*
		Root path is the absolute path to the file passed in relative to the app path. On my dev box, it's
		"/Library/WebServer/Documents/sixd/" + $file
	*/
	public static function getRootPath($file){
		return str_replace('lib/FrontController.php', $file, __FILE__);
	}
	/*
		This is the virtual directory that the app is located in. For instance, I have several sites set up
		on my dev box like http://localhost/sixd/. The virtual path in this case is "sixd".
	*/
	public static function getVirtualPath(){
		return String::replace('/^\//', '', str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']));
	}
	
	/*
		The app path is the path where the sixd code base is located. I have several sites that are running
		the same sixd code base. AppPath is the location of the core code base. On my dev box, it's
		"/Library/WebServer/Documents/sixd"
	*/
	public static function getAppPath(){
		return String::replace('/\/index\.php/', '', $_SERVER['SCRIPT_FILENAME']);
	}
	
	/*
		Document root is full path to the web servers docuemnt root, where it first looks for files upon
		request. On my dev box, it's "/Library/WebServer/Documents/"
	*/
	public static function getDocumentRoot(){
		return String::replace('/' . self::getVirtualPath() . '/', '', self::getAppPath());
	}
	public static function getEncoding(){
		$encoding = $_SERVER["HTTP_ACCEPT_ENCODING"];
		if(headers_sent()){
			$encoding = null;
		}else if(strpos($encoding, 'x-gzip') !== false){
			$encoding = 'x-gzip';
		}else if(strpos($encoding,'gzip') !== false){
			$encoding = 'gzip';
		}else{
			$encoding = null; 
		}
		return $encoding;
	}
	
	public static function getPathInfo(){
		$argv = $_SERVER['argv'];
		$php_self = '';
		$request_uri = $_SERVER['REQUEST_URI'];
		if(array_key_exists('PATH_INFO', $_SERVER)){
			$php_self = $_SERVER['PATH_INFO'];
		}else if(array_key_exists('ORIG_PATH_INFO', $_SERVER)){
			$php_self = $_SERVER['ORIG_PATH_INFO'];
		}else if(count($argv) > 0 && stripos($request_uri, 'index.php') === false){
			if(stripos($argv[0], '?') !== false){
				$argv = explode('?', $argv[0]);
				$php_self = $argv[0];
			}else if(stripos($argv[0], '&') !== false){
				$argv = explode('&', $argv[0]);
				$php_self = $argv[0];
			}else{
				$php_self = $argv[0];
			}
		}else{
			$script_name = str_replace('index.php', '', $_SERVER['SCRIPT_NAME']);
			$php_self = str_replace($script_name, '', $request_uri);
		}
		return $php_self;
	}
	public function execute(){
		$resource_path = 'resources/';
		$path_info = self::getPathInfo();		
		//echo '<br />' . $path_info;
		$parts = explode('/', $path_info);
		$r = null;
		$url_parts = array();
		if($parts !== null && count($parts) > 0){
			$parts = array_filter($parts, array($this, 'isEmpty'));
			foreach($parts as $value){
				$url_parts[] = $value;
			}
			$r = $url_parts[0];
		}

		if($r == null){
			$r = 'index';
		}
		$r = $this->parseForResourceName($r, $url_parts);
		$file_type = $this->parseForFileType($url_parts);
		$resource_name = ucwords($r);
		$class_name = sprintf('%sResource', $resource_name);
		$file = $resource_path . $class_name . '.php';
		if(!file_exists($file)){
			$file = self::getRootPath($file);
		}
		if(file_exists($file)){
			class_exists($class_name) || require($file);
			try{
				$obj = new $class_name(array('url_parts'=>$url_parts));		
				$obj->file_type = $file_type;
				$method = strtolower((array_key_exists('_method', $_REQUEST) ? $_REQUEST['_method'] : $_SERVER['REQUEST_METHOD']));
				ob_start();
				try{					
					$output = Resource::sendMessage($obj, $method, $resource_id);
				}catch(Exception $e){
					switch($e->getCode()){
						case(401):
							$this->delegate->unauthorizedRequestHasOccurred($this, array('file_type'=>$file_type, 'query_string'=>$_SERVER['QUERY_STRING']));
							break;
						case(301):
							$matches = String::find('/href\=\"(.*)\"/', $e->getMessage());
							self::send301Header($matches[1]);
							break;
						default:
							break;
					}
					throw $e;
				}
				if($obj->redirect_parameters != null){
					self::redirectTo($obj->redirect_parameters['resource_name'], $obj->redirect_parameters['query_parameters'], $obj->redirect_parameters['make_secure']);
				}else{
					Resource::sendMessage($obj, 'didFinishLoading');			
				}
			}catch(Exception $e){
				$output .= $this->delegate->exceptionHasOccured($this, array('file_type'=>$file_type, 'query_string'=>$_SERVER['QUERY_STRING'], 'exception'=>$e));
			}
			ob_end_flush();
			$output = $this->trim($output);
			self::$end_time = microtime(true);
			self::sendHeadersForFileType($file_type, strlen($output));
			return $output;
		}else{
			$output = $this->delegate->resourceOrMethodNotFoundDidOccur($this, array('file_type'=>$file_type, 'query_string'=>$_SERVER['QUERY_STRING'], 'server'=>$_SERVER, 'url_parts'=>$url_parts));
			if($output === null){
				self::send404Headers('Resource not found');
			}else{
				self::sendHeadersForFileType($file_type, strlen($output));
				return $output;
			}
		}
	}
	private function trim($text){
		$lines = preg_split('/\n/', $text);
		$upper_bounds = count($lines);
		$temp = '';
		for($i=0; $i < $upper_bounds; $i++){
			$temp = trim($lines[$i]);
			if($temp !== null && strlen($temp) > 0){
				$lines[$i] = $temp;
			}
		}
		$text = join(chr(10), $lines);
		return $text;
	}
	public function isEmpty($value){
		return ($value != null || strlen(trim($value)) > 0);
	}
	
	public static function setNeedsToRedirectToPrevious($callback = null){
		$referer = (array_key_exists('HTTP_REFERER', $_SERVER) ? $_SERVER['HTTP_REFERER'] : null);
		$appendValue = null;
		if($callback != null){
			$appendValue = (is_array($callback) ? $callback[0]->$callback[1]($referer) : $callback($referer));				
		}
		self::setNeedsToRedirectRaw($referer . $appendValue, false);
	}
	public static function setNeedsToRedirectRaw($url){
		header(sprintf('Location: %s', $url));
	}

	public static function redirectTo($url, $params = null, $securely = false){
		self::setNeedsToRedirectRaw(self::urlFor($url, $params, $securely));
	}
	
	public static function requestedUrl(){
		if($_SESSION !== null && array_key_exists('requested_url', $_SESSION)){
			return $_SESSION['requested_url'];
		}else{
			return null;
		}
	}
	public static function setRequestedUrl($value){
		$_SESSION['requested_url'] = $value;
	}
	
	public function errorDidHappen($code, $message, $file, $line, $context){
		$contents = file_get_contents($file, FILE_TEXT);
		$lines = preg_split('/\\n/', $contents);
		self::$error_html = '<code class="error">';
		self::$error_html .= sprintf('<h3>Error code %s: %s</h3>', $code, $message);		
		self::$error_html .= '<ul>';
		foreach(debug_backtrace() as $key=>$value){
			self::$error_html .= sprintf('<li>%d: %s', $key, $value['class']);
			self::$error_html .= sprintf('::%s in %s at line # %d', $value['function'], $value['file'], $value['line']);
			self::$error_html .= '</li>';
		}
		self::$error_html .= '</ul>';
		self::$error_html .= sprintf("<pre>%s</pre>", htmlentities($lines[$line-1]));
		self::$error_html .= '</code>';
		self::notify('errorDidHappen', $this, self::$error_html);
		self::$error_html = null;
		// Make sure this line is commented out in prod because if an error occurs in the database
		// code, it'll display your user name and password.
		//debug_print_backtrace();
	}
	public function exceptionDidHappen($e){
		echo $e;
	}
}
?>