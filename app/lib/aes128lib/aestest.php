<?php
 require_once('aes128.php');
 $aes=new aes128();
 
 $key=$aes->makeKey("0123456789abcdef");//max 16 bytes
 $ct=$aes->blockEncrypt("secretpass",$key);//max data size: 16 bytes
 
 $cpt=$aes->blockDecrypt($ct,$key);	
 echo $cpt;
   
?>