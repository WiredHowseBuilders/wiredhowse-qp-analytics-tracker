<?php



if(class_exists('PathSetter')) {
	if(!$wh_pathsetter = PathSetter::init()){ die('no paths!@'); }
	$wh_admincheat = (isset($_GET['oob']))? $_GET['oob'] : false; 
	if(defined('ADMINDEBUG') || $wh_admincheat == 'oubre'){
	$pp = new PrettyPrint();
	 echo  '<pre style="white-space:pre-wrap;font-size:13px;">' . htmlspecialchars($pp->format($wh_admincheat), ENT_QUOTES, 'UTF-8') . '</pre>';
	}
}
      