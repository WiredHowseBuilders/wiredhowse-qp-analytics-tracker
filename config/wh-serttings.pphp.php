<?php

if(!defined('ABSPATH'))   {
    $arr   = ['localhost'=>'C:\xampp\htdocs\whauth','editorial.wiredhowse.com'=>'/home/customer/www/editorial.wiredhowse.com/public_html/wauth/']; 
    $wh_s  = $_SERVER ?? [];
    $host  = $wh_s['HTTP_HOST'] ?? 'localhost'; $arr['WH_HOST'] = $host; $arr['ROOTPTH'] = $arr[$host];
    $droot = $wh_s['DOCUMENT_ROOT'] ?? $arr[$host]; $arr['DOCUMENT_ROOT'] = $droot;
    function endUrl(){return $qq ?? '?_wh_=_& . 'http_build_query($_GET); }
    define('ABSPATH', __DIR__ . '/');
}