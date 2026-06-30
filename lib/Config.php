<?php


$filename = pathinfo(__FILE__, PATHINFO_FILENAME);
$fullname = __DIR__ . DIRECTORY_SEPARATOR . $filename . '.class.php';

echo "Looking for: " . $fullname . "<br>";
echo "Current directory: " . __DIR__ . "<br>";

if (file_exists($fullname)) {
    echo "Status: Found";
} else {
    echo "Status: Not Found";
}

$filename = pathinfo(__FILE__, PATHINFO_FILENAME);
$fullname = __DIR__ . DIRECTORY_SEPARATOR . $filename . '.class.php';

if (!class_exists($filename)) {
if (file_exists($fullname)) {
    include $fullname;
} 
} else {
    echo "The App Name is: " . whConfig::$WHAPP;
    echo "The JS Path is: " . whConfig::$WHJSURL;
}


