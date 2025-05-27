<?php
// Security headers middleware for Quiz API
class SecurityHeaders {
    
    public static function apply() {
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Enable XSS protection (for legacy browsers)
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (restrictive for API)
        header("Content-Security-Policy: default-src 'none'; script-src 'none'; object-src 'none'");
        
        // HSTS for HTTPS (only add when actually using HTTPS)
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
        }
        
        // Feature policy (restrict browser features)
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
        
        // CORS headers for API usage
        $allowedOrigins = [
            'http://localhost:3000',
            'http://localhost:8080',
            'http://127.0.0.1:8080'
        ];
        
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400'); // 24 hours
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    public static function applyRateLimit() {
        // Simple rate limiting implementation
        $clientIp = self::getClientIP();
        $cacheFile = sys_get_temp_dir() . "/quiz_api_rate_" . md5($clientIp);
        
        $maxRequests = 100; // requests per window
        $windowSeconds = 3600; // 1 hour window
        
        $currentTime = time();
        $requests = [];
        
        // Load existing requests from cache
        if (file_exists($cacheFile)) {
            $requests = json_decode(file_get_contents($cacheFile), true) ?: [];
        }
        
        // Remove old requests outside the window
        $requests = array_filter($requests, function($timestamp) use ($currentTime, $windowSeconds) {
            return ($currentTime - $timestamp) < $windowSeconds;
        });
        
        // Check if rate limit exceeded
        if (count($requests) >= $maxRequests) {
            header('HTTP/1.1 429 Too Many Requests');
            header('Retry-After: ' . $windowSeconds);
            header('X-RateLimit-Limit: ' . $maxRequests);
            header('X-RateLimit-Remaining: 0');
            header('X-RateLimit-Reset: ' . ($currentTime + $windowSeconds));
            
            echo json_encode([
                'error' => 'Rate limit exceeded',
                'message' => "Maximum $maxRequests requests per hour allowed",
                'retry_after' => $windowSeconds
            ]);
            exit();
        }
        
        // Add current request
        $requests[] = $currentTime;
        
        // Save to cache
        file_put_contents($cacheFile, json_encode($requests), LOCK_EX);
        
        // Add rate limit headers
        header('X-RateLimit-Limit: ' . $maxRequests);
        header('X-RateLimit-Remaining: ' . ($maxRequests - count($requests)));
        header('X-RateLimit-Reset: ' . ($currentTime + $windowSeconds));
    }
    
    private static function getClientIP() {
        // Get client IP address (handle various proxy scenarios)
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                if (!empty($ip)) {
                    // Handle comma-separated IPs (from proxies)
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                    
                    // Validate IP address
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                        return $ip;
                    }
                }
            }
        }
        
        // Fallback to REMOTE_ADDR
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
