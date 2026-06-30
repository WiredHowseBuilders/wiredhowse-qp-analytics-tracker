<?php
if(!defined('WH_CONFIG')) { 
PageRenderer::render('403', 'public');
 }


  $ip = new IPAddress();
  if ($ip->isInArray(['172.56.93.53','172.56.90.191','2001:4860:7:30e::df'])) {
    echo "console.log('admin_ok');window.onload = function() {localStorage.setItem('_wh_oob', true);};"; 

    } else {
        echo "console.log('admin_not_ok');window.onload = function() {localStorage.setItem('_wh_oob', false);};   
        window.location.replace('/public/404.php'); 
        //window.location.href ='/404.php;"; 
    }

    ?>