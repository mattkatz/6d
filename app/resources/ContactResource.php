<?php
class_exists('AppResource') || require('AppResource.php');
class ContactResource extends AppResource{
	public function __construct($attributes = null){
		parent::__construct($attributes);
	}
	public function __destruct(){
		parent::__destruct();
	}
	
	public function post($email = null){
		if($email !== null){
			if($this->send(array($this->config->email), $email, 'Somebody wants to be notified about 6d when it launches')){
				self::setUserMessage("Thank you for your interest. We'll keep you updated.");
			}else{
				self::setUserMessage("Something didn't go right.");
			}
		}
		$this->redirectTo(null, array(''=>'#thanks'));
	}
	
	private function send($emails, $message=null, $subject){
		$to = implode($emails,",");
		$headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=iso-8859-1\r\nFrom: webmaster@get6d.com\r\nReply-To: webmaster@get6d.com\r\nX-Mailer: PHP/" . phpversion();

		if($emails[0] !== null && strlen($emails[0]) > 0){
			if(mail($to, $subject, $message, $headers))
				return true;
			else
				return false;
		}else{
			return false;
		}
	}
	
	
}

?>