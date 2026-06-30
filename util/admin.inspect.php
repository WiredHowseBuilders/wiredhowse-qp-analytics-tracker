<?php

//============================================



//============================================

// include_once('../inc-config.php');
;
$constants = get_defined_constants(true)['user'];
foreach ($constants as $name => $value) {
    if (str_starts_with($name, 'WH_')) {
        echo $name . ' = ';
        var_dump($value);
    }
}

echo '<pre>';
print_r(get_defined_constants(true)['user']);
echo '</pre>';

	$pp = new PrettyPrint();
	 echo  '<pre style="white-space:pre-wrap;font-size:13px;">' . htmlspecialchars($pp->format(PathInspector::collect()), ENT_QUOTES, 'UTF-8') . '</pre>';


	 // Load dependencies
// FileLoader::incs('class', 'Env.php');
// FileLoader::incs('class', 'PrettyPrint.php'); 
// FileLoader::incs('class', 'PathInspector.php'); 
// FileLoader::incs('class', 'QueryParam.php'); 
// FileLoader::incs('class', 'ClickBankAffiliateHopTracker.php'); 



// PathInspector::collect();
// PathInspector::getKee('server');
// PathInspector::findKeyRec('DOCUMENT_ROOT');
// echo '<pre>';
// print_r(PathInspector::collect());
// echo '</pre>';



			// $wh_pathsetter = PathInspector::collect();



// if(class_exists('PathSetter')) {
// 	if(!$wh_pathsetter = PathSetter::init()){ die('no paths!@'); }
// 	$wh_admincheat = (isset($_GET['oob']))? $_GET['oob'] : false; 
// 	if(defined('ADMINDEBUG') || $wh_admincheat == 'oubre'){
// 	$pp = new PrettyPrint();
// 	 echo  '<pre style="white-space:pre-wrap;font-size:13px;">' . htmlspecialchars($pp->format($wh_admincheat), ENT_QUOTES, 'UTF-8') . '</pre>';
// 	}
// }
