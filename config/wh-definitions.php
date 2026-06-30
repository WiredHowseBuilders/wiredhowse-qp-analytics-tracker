<?php



if(!defined('WH_PATH'))   {define('WH_PATH',__DIR__);}	$arr['WH_PATH'] = WH_PATH; 
if(!defined('WH_APP'))    {define('WH_APP','whauth');}	$arr['WH_APP'] = WH_APP; 
if(!defined('WH_CONFIG')) {define('WH_CONFIG',ABSPATH . WH_APP . '/wh-config.php'); } $arr['WH_CONFIG'] = WH_CONFIG; 
if(!defined('WH_HOST'))   {define('WH_HOST',$host);} $arr['WH_HOST'] = WH_HOST; 
if(!defined('WH_URL'))   {define('WH_URLPATH','https://' . WH_HOST . '/' . WH_APP . '/'); define('WH_URL','https://' . WH_HOST . '/' . WH_APP . '/'); } $arr['WH_URLPATH'] = WH_URLPATH; $arr['WH_URL'] = WH_URL; 
//=======================================
//////////////////////////////////////////////////////////////////////////////////////
//=======================================
// Database configuration
if('localhost' !== $host){
	if(!defined('DB_HOST')) {define('DB_HOST', 'localhost');}  
	if(!defined('DB_NAME')) {define('DB_NAME', 'dbvcfdgz9bmrrv');}  
	if(!defined('DB_USER')) {define('DB_USER', 'ulwg6jyi9aifw');}  
	if(!defined('DB_PASS')) {define('DB_PASS', 'dr4G0nmaster11!');}  
} else {
	if(!defined('DB_HOST')) {define('DB_HOST', 'localhost');}
	if(!defined('DB_NAME')) {define('DB_NAME', 'dbwea3xblsgqxk');}
	if(!defined('DB_USER')) {define('DB_USER', 'timmyWngg');}
	if(!defined('DB_PASS')) {define('DB_PASS', 'dr4G0nmaster11!');}
}

	if(!defined('AUTH_ENCRYPTION_KEY')) {define('AUTH_ENCRYPTION_KEY', '41316db758f6a9e6a6778fae31fde544');}
	if(!defined('AUTH_BASE_URL')) {define('AUTH_BASE_URL', WH_URLPATH . 'auth.php');}
	if(!defined('AUTH_EMAIL_FROM')) {define('AUTH_EMAIL_FROM', 'posty@wiredhowse.com');}
	if(!defined('AUTH_EMAIL_NAME')) {define('AUTH_EMAIL_NAME', 'wiredHowse Builders Coop');}
	//--------------------------------------------
	//define('LOGIN_URL', WH_URLPATH . 'login.php');
	if(!defined('LOGIN_URL')) {define('LOGIN_URL', WH_URLPATH .  endUrl('/',['pg'=>'login','pass'=>'pass123']));}
	if(!defined('AUTH_LOGIN_URL')) {define('AUTH_LOGIN_URL', LOGIN_URL);}
	//=======================================

	if(!defined('AUTH_DB_HOST')) {define('AUTH_DB_HOST', DB_HOST);}     // Reuse your DB
	if(!defined('AUTH_DB_NAME')) {define('AUTH_DB_NAME', DB_NAME);}    
	if(!defined('AUTH_DB_USER')) {define('AUTH_DB_USER', DB_USER);}    
	if(!defined('AUTH_DB_PASS')) {define('AUTH_DB_PASS', DB_PASS);}


	if(!defined('AUTH_SMTP_HOST')) {define('AUTH_SMTP_HOST', 'mail.wiredhowse.com');}
	if(!defined('AUTH_SMTP_PORT')) {define('AUTH_SMTP_PORT', 465);}
	if(!defined('AUTH_SMTP_USER')) {define('AUTH_SMTP_USER', 'koubre@wiredhowse.com');}
	if(!defined('AUTH_SMTP_PASS')) {define('AUTH_SMTP_PASS', 'dr4G0nmaster11!');}  // Change this

$arr['DB_HOST'] = DB_HOST;$arr['DB_NAME'] = DB_NAME;$arr['DB_USER'] = DB_USER;$arr['DB_PASS'] = DB_PASS;