<?php


    $arr= ['localhost'=>'C:\xampp\htdocs\whauth','editorial.wiredhowse.com'=>'/home/customer/www/editorial.wiredhowse.com/public_html/wauth/']; 
    $wh_s  = $_SERVER ?? [];
    $host  = $wh_s['HTTP_HOST'] ?? 'localhost';
    $droot = $wh_s['DOCUMENT_ROOT'] ?? $arr[$host];
    if(!defined('ABSPATH'))   {define('ABSPATH', __DIR__ . '/');}