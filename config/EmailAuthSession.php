<?php
/**
 * EmailAuthSession - Email-based authentication with localStorage persistence
 * 
 * Implements flowchart logic for user authentication using:
 * - Email-based auth tokens sent via link
 * - MySQL storage with encrypted data
 * - localStorage for client-side persistence
 * - Role-based session durations (fresh/medium/max)
 * - 3-hour maximum session with re-evaluation
 * 
 * Usage:
 *   $auth = new EmailAuthSession($pdo);
 *   $auth->initSession(); // Call at start of each page request
 *   
 *   if ($auth->isAuthenticated()) {
 *       // User is authenticated
 *   } else {
 *       // Handle according to $auth->getState()
 *   }
 */
class EmailAuthSession {
    
    private $pdo;
    private $encryptionKey;
    
    // Session states
    const STATE_AUTHENTICATED = 'authenticated';
    const STATE_NEED_EMAIL = 'need_email';
    const STATE_SENT_LINK = 'sent_link';
    const STATE_INVALID = 'invalid';
    
    // Role-based session durations (in seconds)
    const ROLE_FRESH = 3600;    // 1 hour
    const ROLE_MEDIUM = 10800;  // 3 hours
    const ROLE_MAX = 18000;     // 5 hours
    
    // Max session before re-evaluation
    const MAX_SESSION = 10800;  // 3 hours
    
    private $currentState;
    private $userData = null;
    
    /**
     * Constructor
     * 
     * @param PDO $pdo Database connection
     * @param string $encryptionKey Encryption key for data (32 characters recommended)
     */
    public function __construct($pdo, $encryptionKey = null) {
        $this->pdo = $pdo;
        $this->encryptionKey = $encryptionKey ?? $this->getDefaultKey();
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Initialize session - call this at the start of each page request
     * Implements the flowchart logic
     */
    public function initSession() {
        // Step 1: Is there a SESSION?
        if (isset($_SESSION['auth_data'])) {
            // Step 2: Is Valid?
            if ($this->isSessionValid()) {
                // Sync localStorage & localSession
                $this->currentState = self::STATE_AUTHENTICATED;
                $this->userData = $_SESSION['auth_data'];
                return true;
            } else {
                // Not valid - check localStorage & localSession
                return $this->handleInvalidSession();
            }
        } else {
            // No session - check localStorage via client data
            return $this->handleNoSession();
        }
    }
    
    /**
     * Check if session is valid based on timestamp and duration
     */
    private function isSessionValid() {
        if (!isset($_SESSION['auth_data']['session_start']) || 
            !isset($_SESSION['auth_data']['role'])) {
            return false;
        }
        
        $sessionStart = $_SESSION['auth_data']['session_start'];
        $role = $_SESSION['auth_data']['role'];
        $currentTime = time();
        
        // Get role duration
        $duration = $this->getRoleDuration($role);
        
        // Check if session has expired
        $elapsed = $currentTime - $sessionStart;
        
        // Also check max session time (3 hours)
        if ($elapsed > self::MAX_SESSION || $elapsed > $duration) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Handle invalid session - check localStorage data
     */
    private function handleInvalidSession() {
        // Check if we have localStorage data posted from client
        if (isset($_POST['local_email']) && isset($_POST['local_id'])) {
            $localEmail = $_POST['local_email'];
            $localId = $_POST['local_id'];
            
            // Decrypt localStorage data
            $email = $this->decrypt($localEmail);
            $uuid = $this->decrypt($localId);
            
            if ($email && $uuid) {
                // Verify hash from database
                $user = $this->getUserByEmailAndUUID($email, $uuid);
                
                if ($user) {
                    // Valid - reset session, update log in MySQL
                    $this->resetSession($user);
                    $this->logActivity($user['id'], 'session_reset');
                    $this->currentState = self::STATE_AUTHENTICATED;
                    return true;
                } else {
                    // Hash not valid - clear all data
                    $this->clearAllData();
                    $this->currentState = self::STATE_NEED_EMAIL;
                    return false;
                }
            } else {
                // Decryption failed - clear all data
                $this->clearAllData();
                $this->currentState = self::STATE_NEED_EMAIL;
                return false;
            }
        } else {
            // No localStorage data - need email
            $this->currentState = self::STATE_NEED_EMAIL;
            return false;
        }
    }
    
    /**
     * Handle no session - check if localStorage exists
     */
    private function handleNoSession() {
        // This requires client-side cooperation
        // Check if we have localStorage data
        if (isset($_POST['local_email']) && isset($_POST['local_id'])) {
            // We have local data - validate it
            return $this->handleInvalidSession();
        } else {
            // No local data - need email
            $this->currentState = self::STATE_NEED_EMAIL;
            return false;
        }
    }
    
    /**
     * Request authentication for an email address
     * Generates auth token and returns link to send via email
     * 
     * @param string $email User's email address
     * @param string $role Role (fresh, medium, max)
     * @param string $baseUrl Base URL for auth links
     * @return array ['success' => bool, 'link' => string, 'masked_email' => string]
     */
    public function requestAuth($email, $role = 'medium', $baseUrl = '') {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Invalid email address'];
        }
        
        // Validate role
        if (!in_array($role, ['fresh', 'medium', 'max'])) {
            $role = 'medium';
        }
        
        // Check if user exists
        $user = $this->getUserByEmail($email);
        
        if (!$user) {
            // Create new user
            $uuid = $this->generateUUID();
            $userId = $this->createUser($email, $uuid, $role);
        } else {
            // Update existing user
            $uuid = $this->decrypt($user['uuid_encrypted']);
            $userId = $user['id'];
            $this->updateUserRole($userId, $role);
        }
        
        // Generate auth token
        $token = $this->generateAuthToken($userId, $email, $uuid);
        
        // Store token in database
        $this->storeAuthToken($userId, $token);
        
        // Create auth link
        $authLink = rtrim($baseUrl, '/') . '?auth_token=' . urlencode($token);
        
        // Get masked email for display
        $maskedEmail = $this->maskEmail($email);
        
        $this->currentState = self::STATE_SENT_LINK;
        
        return [
            'success' => true,
            'link' => $authLink,
            'masked_email' => $maskedEmail,
            'message' => "Email sent to address ending in {$maskedEmail}"
        ];
    }
    
    /**
     * Validate auth token from email link
     * 
     * @param string $token Auth token from URL
     * @return bool Success
     */
    public function validateAuthToken($token) {
        // Look up token in database
        $stmt = $this->pdo->prepare("
            SELECT u.*, t.created_at 
            FROM auth_users u
            JOIN auth_tokens t ON u.id = t.user_id
            WHERE t.token = ? AND t.used = 0
            ORDER BY t.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            return false;
        }
        
        // Check if token is expired (24 hours)
        $tokenAge = time() - strtotime($result['created_at']);
        if ($tokenAge > 86400) {
            return false;
        }
        
        // Mark token as used
        $this->markTokenUsed($token);
        
        // Create session
        $this->createSession($result);
        
        // Log activity
        $this->logActivity($result['id'], 'login_via_token');
        
        return true;
    }
    
    /**
     * Create authenticated session
     */
    private function createSession($user) {
        $_SESSION['auth_data'] = [
            'user_id' => $user['id'],
            'email' => $this->decrypt($user['email_encrypted']),
            'uuid' => $this->decrypt($user['uuid_encrypted']),
            'role' => $user['role'],
            'session_start' => time(),
            'session_hash' => $this->generateSessionHash($user['id'])
        ];
        
        $this->userData = $_SESSION['auth_data'];
        $this->currentState = self::STATE_AUTHENTICATED;
    }
    
    /**
     * Reset session with existing user data
     */
    private function resetSession($user) {
        $this->createSession($user);
    }
    
    /**
     * Get encrypted data for localStorage (call via AJAX)
     * Returns JSON with encrypted email and ID
     */
    public function getLocalStorageData() {
        if (!$this->isAuthenticated()) {
            return ['success' => false];
        }
        
        $email = $_SESSION['auth_data']['email'];
        $uuid = $_SESSION['auth_data']['uuid'];
        
        return [
            'success' => true,
            'encrypted_email' => $this->encrypt($email),
            'encrypted_id' => $this->encrypt($uuid),
            'session_hash' => $_SESSION['auth_data']['session_hash']
        ];
    }
    
    /**
     * Clear all session and data
     */
    public function clearAllData() {
        // Clear PHP session
        unset($_SESSION['auth_data']);
        
        // Return signal to clear localStorage (handle client-side)
        $this->currentState = self::STATE_INVALID;
    }
    
    /**
     * Check if user is currently authenticated
     */
    public function isAuthenticated() {
        return $this->currentState === self::STATE_AUTHENTICATED && 
               isset($_SESSION['auth_data']);
    }
    
    /**
     * Get current state
     */
    public function getState() {
        return $this->currentState;
    }
    
    /**
     * Get current user data
     */
    public function getUserData() {
        return $this->userData;
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['auth_data']['user_id'])) {
            $this->logActivity($_SESSION['auth_data']['user_id'], 'logout');
        }
        $this->clearAllData();
    }
    
    // ============================================================
    // DATABASE OPERATIONS
    // ============================================================
    
    /**
     * Get user by email
     */
    private function getUserByEmail($email) {
        $emailHash = hash('sha256', strtolower($email));
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM auth_users 
            WHERE email_hash = ?
            LIMIT 1
        ");
        $stmt->execute([$emailHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get user by email and UUID
     */
    private function getUserByEmailAndUUID($email, $uuid) {
        $emailHash = hash('sha256', strtolower($email));
        $uuidHash = hash('sha256', $uuid);
        
        $stmt = $this->pdo->prepare("
            SELECT * FROM auth_users 
            WHERE email_hash = ? AND uuid_hash = ?
            LIMIT 1
        ");
        $stmt->execute([$emailHash, $uuidHash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Create new user
     */
    private function createUser($email, $uuid, $role) {
        $emailHash = hash('sha256', strtolower($email));
        $uuidHash = hash('sha256', $uuid);
        $emailEncrypted = $this->encrypt($email);
        $uuidEncrypted = $this->encrypt($uuid);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO auth_users 
            (email_hash, uuid_hash, email_encrypted, uuid_encrypted, role, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$emailHash, $uuidHash, $emailEncrypted, $uuidEncrypted, $role]);
        
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Update user role
     */
    private function updateUserRole($userId, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE auth_users 
            SET role = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$role, $userId]);
    }
    
    /**
     * Store auth token
     */
    private function storeAuthToken($userId, $token) {
        $stmt = $this->pdo->prepare("
            INSERT INTO auth_tokens 
            (user_id, token, created_at, used)
            VALUES (?, ?, NOW(), 0)
        ");
        $stmt->execute([$userId, $token]);
    }
    
    /**
     * Mark token as used
     */
    private function markTokenUsed($token) {
        $stmt = $this->pdo->prepare("
            UPDATE auth_tokens 
            SET used = 1, used_at = NOW()
            WHERE token = ?
        ");
        $stmt->execute([$token]);
    }
    
    /**
     * Log user activity
     */
    private function logActivity($userId, $activity) {
        $stmt = $this->pdo->prepare("
            INSERT INTO auth_activity_log 
            (user_id, activity, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $userId, 
            $activity,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);
    }
    
    // ============================================================
    // UTILITY METHODS
    // ============================================================
    
    /**
     * Generate UUID v4
     */
    private function generateUUID() {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
    
    /**
     * Generate auth token
     */
    private function generateAuthToken($userId, $email, $uuid) {
        $data = $userId . '|' . $email . '|' . $uuid . '|' . time() . '|' . random_bytes(16);
        return bin2hex(hash('sha256', $data, true));
    }
    
    /**
     * Generate session hash
     */
    private function generateSessionHash($userId) {
        $data = $userId . '|' . time() . '|' . random_bytes(16);
        return hash('sha256', $data);
    }
    
    /**
     * Get role duration in seconds
     */
    private function getRoleDuration($role) {
        switch ($role) {
            case 'fresh':
                return self::ROLE_FRESH;
            case 'medium':
                return self::ROLE_MEDIUM;
            case 'max':
                return self::ROLE_MAX;
            default:
                return self::ROLE_MEDIUM;
        }
    }
    
    /**
     * Mask email for display
     */
    private function maskEmail($email) {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return 'xxx';
        }
        
        $domain = $parts[1];
        return 'xxx' . substr($domain, -4);
    }
    
    /**
     * Encrypt data
     */
    private function encrypt($data) {
        $iv = random_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
    
    /**
     * Decrypt data
     */
    private function decrypt($data) {
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        return openssl_decrypt($encrypted, 'AES-256-CBC', $this->encryptionKey, 0, $iv);
    }
    
    /**
     * Get default encryption key
     * YOU SHOULD OVERRIDE THIS WITH YOUR OWN KEY
     */
    private function getDefaultKey() {
        return hash('sha256', 'your-secret-key-change-this-' . $_SERVER['SERVER_NAME'] ?? 'default');
    }
    
    /**
     * Create database tables
     */
    public function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS auth_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email_hash VARCHAR(64) NOT NULL,
            uuid_hash VARCHAR(64) NOT NULL,
            email_encrypted TEXT NOT NULL,
            uuid_encrypted TEXT NOT NULL,
            role ENUM('fresh', 'medium', 'max') DEFAULT 'medium',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            UNIQUE KEY (email_hash),
            KEY (uuid_hash)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS auth_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token VARCHAR(128) NOT NULL,
            created_at DATETIME NOT NULL,
            used TINYINT(1) DEFAULT 0,
            used_at DATETIME NULL,
            KEY (user_id),
            UNIQUE KEY (token),
            FOREIGN KEY (user_id) REFERENCES auth_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        
        CREATE TABLE IF NOT EXISTS auth_activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            activity VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            created_at DATETIME NOT NULL,
            KEY (user_id),
            KEY (created_at),
            FOREIGN KEY (user_id) REFERENCES auth_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ";
        
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $this->pdo->exec($statement);
            }
        }
    }
}
