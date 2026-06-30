<?php


$validPassword = Env::get('CB_DASHBOARD_PASSWORD', 'changeme123');
$providedPassword = QueryParam::get('pass');
if ($providedPassword !== $validPassword) {

    http_response_code(403);

    die('

    <!DOCTYPE html>

    <html>

    <head><title>Access Denied</title></head>

    <body style="font-family: Arial; padding: 50px; text-align: center;">

        <h1>🔒 Access Denied</h1>

        <p>Add ?pass=YOUR_PASSWORD to the URL</p>

    </body>

    </html>

    ');

} else {
    if(!defined('VPASS')){
        define('VPASS', $validPassword);
    }
}


if(defined('VPASS')) {
    $validPassword = VPASS; 
    $validpassword = VPASS; 
} 