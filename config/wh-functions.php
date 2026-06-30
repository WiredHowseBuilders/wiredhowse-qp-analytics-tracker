<?php



if(!function_exists('whLoadD')){
	function whLoadD($k=false,$v=false,$arr=[]){$array = $arr ?? []; if(!array_key_exists($k, $array)){$array[$k] = $v; }return $array; }
}

if(!function_exists('getPP')){
function getPP($arr){
	$arr = $arr ?? []; 
	$dta = ''; 
	$pp = new PrettyPrint();
	$dta .='<pre style="white-space:pre-wrap;font-size:13px;">'; 
	$dta .= htmlspecialchars($pp->format($arr), ENT_QUOTES, 'UTF-8');
	$dta .= '</pre>';
	return $dta; 

}
}
if(!function_exists('showPP')){
function showPP($arr=[]){
	$arr = $arr ?? []; 
	echo retPP($arr); 
}
}
if(!function_exists('retPP')){
function retPP($arr=[]){
	return getPP($arr);

}
}



// Create PDO connection
function getAuthPDO() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Initialize auth session helper function
function getAuthSession() {
    require_once __DIR__ . '/EmailAuthSession.php';
    return new EmailAuthSession(getAuthPDO(), AUTH_ENCRYPTION_KEY);
}

// Get EmailSender instance for authentication emails
function getAuthEmailSender() {
    static $sender = null;
    
    if ($sender === null) {
        require_once ABSPATH . 'helpers/' . 'EmailSender.php';
        $sender = new EmailSender(
            SMTP_HOST,
            SMTP_PORT,
            SMTP_USERNAME,
            SMTP_PASSWORD,
            AUTH_EMAIL_FROM,
            AUTH_EMAIL_NAME
        );
    }
    
    return $sender;
}

// Send email helper function using SMTP (NOT mail() function)
function sendAuthEmail($to, $authLink, $maskedEmail) {
    $subject = 'Your Authentication Link';
    
    $message = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .button { 
                display: inline-block; 
                padding: 12px 24px; 
                background: #007bff; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px;
                margin: 20px 0;
            }
            .footer { color: #666; font-size: 12px; margin-top: 30px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>Authentication Request</h2>
            <p>Click the button below to authenticate and access your account:</p>
            <a href='{$authLink}' class='button'>Authenticate Now</a>
            <p><small>This link is being sent to an email ending in <strong>{$maskedEmail}</strong></small></p>
            <p><small>This link will expire in 24 hours.</small></p>
            <div class='footer'>
                <p>If you didn't request this, please ignore this email.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    try {
        // Use EmailSender with SMTP instead of mail() function
        $sender = getAuthEmailSender();
        return $sender->sendEmail($to, $subject, $message);
    } catch (Exception $e) {
        error_log("Auth email send failed: " . $e->getMessage());
        return false;
    }
}
