/**
 * EmailAuthClient - Client-side localStorage handling for EmailAuthSession
 * 
 * Handles:
 * - localStorage persistence of encrypted credentials
 * - Automatic sync with server session
 * - Session validation on page load
 * - Clear data on logout/invalid state
 */
class EmailAuthClient {
    
    constructor(options = {}) {
        this.storagePrefix = options.storagePrefix || 'auth_';
        this.syncEndpoint = options.syncEndpoint || '/auth_sync.php';
        this.validateEndpoint = options.validateEndpoint || '/auth_validate.php';
        this.autoSync = options.autoSync !== false;
        
        // Storage keys
        this.keys = {
            email: this.storagePrefix + 'email',
            id: this.storagePrefix + 'id',
            hash: this.storagePrefix + 'hash',
            timestamp: this.storagePrefix + 'timestamp'
        };
        
        if (this.autoSync) {
            this.init();
        }
    }
    
    /**
     * Initialize - check and sync on page load
     */
    async init() {
        // Check if we have local data
        const hasLocal = this.hasLocalData();
        
        if (hasLocal) {
            // We have local data - send to server for validation
            await this.validateWithServer();
        } else {
            // No local data - check if server has session
            await this.syncFromServer();
        }
    }
    
    /**
     * Check if localStorage has auth data
     */
    hasLocalData() {
        return localStorage.getItem(this.keys.email) !== null && 
               localStorage.getItem(this.keys.id) !== null;
    }
    
    /**
     * Get localStorage data
     */
    getLocalData() {
        return {
            email: localStorage.getItem(this.keys.email),
            id: localStorage.getItem(this.keys.id),
            hash: localStorage.getItem(this.keys.hash),
            timestamp: localStorage.getItem(this.keys.timestamp)
        };
    }
    
    /**
     * Save to localStorage
     */
    saveLocalData(email, id, hash) {
        localStorage.setItem(this.keys.email, email);
        localStorage.setItem(this.keys.id, id);
        localStorage.setItem(this.keys.hash, hash);
        localStorage.setItem(this.keys.timestamp, Date.now());
    }
    
    /**
     * Clear all localStorage data
     */
    clearLocalData() {
        localStorage.removeItem(this.keys.email);
        localStorage.removeItem(this.keys.id);
        localStorage.removeItem(this.keys.hash);
        localStorage.removeItem(this.keys.timestamp);
    }
    
    /**
     * Validate local data with server
     */
    async validateWithServer() {
        const localData = this.getLocalData();
        
        if (!localData.email || !localData.id) {
            return false;
        }
        
        try {
            const response = await fetch(this.validateEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    local_email: localData.email,
                    local_id: localData.id
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Server validated - update hash if provided
                if (result.session_hash) {
                    localStorage.setItem(this.keys.hash, result.session_hash);
                }
                return true;
            } else {
                // Server rejected - clear local data
                this.clearLocalData();
                
                // Redirect to login if specified
                if (result.redirect) {
                    window.location.href = result.redirect;
                }
                
                return false;
            }
        } catch (error) {
            console.error('Auth validation error:', error);
            return false;
        }
    }
    
    /**
     * Sync from server session to localStorage
     */
    async syncFromServer() {
        try {
            const response = await fetch(this.syncEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (result.success && result.encrypted_email && result.encrypted_id) {
                // Server has session - save to localStorage
                this.saveLocalData(
                    result.encrypted_email,
                    result.encrypted_id,
                    result.session_hash
                );
                return true;
            } else {
                // No server session
                return false;
            }
        } catch (error) {
            console.error('Auth sync error:', error);
            return false;
        }
    }
    
    /**
     * Logout - clear everything
     */
    async logout(logoutEndpoint = '/auth_logout.php') {
        // Clear localStorage
        this.clearLocalData();
        
        // Call server logout
        try {
            await fetch(logoutEndpoint, {
                method: 'POST',
                credentials: 'same-origin'
            });
        } catch (error) {
            console.error('Logout error:', error);
        }
        
        // Redirect to home or login
        window.location.href = '/';
    }
    
    /**
     * Handle session expiry warning
     * Call this periodically to check session age
     */
    checkSessionAge(warningCallback, expiredCallback) {
        const timestamp = localStorage.getItem(this.keys.timestamp);
        
        if (!timestamp) {
            return;
        }
        
        const age = Date.now() - parseInt(timestamp);
        const threeHours = 3 * 60 * 60 * 1000; // 3 hours in milliseconds
        const warningTime = 2.5 * 60 * 60 * 1000; // 2.5 hours
        
        if (age > threeHours) {
            // Session expired
            if (expiredCallback) {
                expiredCallback();
            }
            this.clearLocalData();
        } else if (age > warningTime) {
            // Warning - session will expire soon
            if (warningCallback) {
                const remaining = Math.floor((threeHours - age) / 60000); // minutes
                warningCallback(remaining);
            }
        }
    }
    
    /**
     * Extend session - useful for "keep me logged in" functionality
     */
    async extendSession(extendEndpoint = '/auth_extend.php') {
        try {
            const response = await fetch(extendEndpoint, {
                method: 'POST',
                credentials: 'same-origin'
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Update timestamp
                localStorage.setItem(this.keys.timestamp, Date.now());
                return true;
            }
            
            return false;
        } catch (error) {
            console.error('Session extend error:', error);
            return false;
        }
    }
}

// Auto-initialize if DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        window.authClient = new EmailAuthClient();
    });
} else {
    window.authClient = new EmailAuthClient();
}

// Optional: Check session age every 5 minutes
setInterval(function() {
    if (window.authClient) {
        window.authClient.checkSessionAge(
            // Warning callback (30 minutes remaining)
            function(minutesRemaining) {
                console.log('Session expires in ' + minutesRemaining + ' minutes');
                // You could show a warning banner here
            },
            // Expired callback
            function() {
                console.log('Session expired');
                // Redirect to login
                window.location.href = '/?expired=1';
            }
        );
    }
}, 5 * 60 * 1000); // Every 5 minutes