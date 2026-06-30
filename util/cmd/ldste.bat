ipconfig /flushdns
timeout /t 1
start php -S localhost:8000 -t H:\Development\Repositories\wiredhowse-builders-php-c
timeout /t 2
start http://localhost:8000
