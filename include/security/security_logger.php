<?php
// Security logging and monitoring system
class SecurityLogger {
    private static $logFile = __DIR__ . '/../../logs/security.log';
    
    public static function logSecurityEvent(string $event, string $level = 'INFO', array $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $clientIp = self::getClientIP();
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $requestUri = $_SERVER['REQUEST_URI'] ?? 'Unknown';
        $method = $_SERVER['REQUEST_METHOD'] ?? 'Unknown';
        
        $logEntry = [
            'timestamp' => $timestamp,
            'level' => $level,
            'event' => $event,
            'client_ip' => $clientIp,
            'user_agent' => $userAgent,
            'request_uri' => $requestUri,
            'method' => $method,
            'context' => $context
        ];
        
        $logLine = json_encode($logEntry) . PHP_EOL;
        
        // Ensure log directory exists
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file
        file_put_contents(self::$logFile, $logLine, FILE_APPEND | LOCK_EX);
        
        // For critical events, also log to error log
        if ($level === 'CRITICAL' || $level === 'ERROR') {
            error_log("SECURITY: $event - IP: $clientIp - URI: $requestUri");
        }
    }
    
    public static function logSQLInjectionAttempt(string $suspiciousInput) {
        self::logSecurityEvent(
            'SQL injection attempt detected',
            'CRITICAL',
            ['suspicious_input' => $suspiciousInput]
        );
    }
    
    public static function logXSSAttempt(string $suspiciousInput) {
        self::logSecurityEvent(
            'XSS attempt detected',
            'CRITICAL',
            ['suspicious_input' => $suspiciousInput]
        );
    }
    
    public static function logRateLimitExceeded() {
        self::logSecurityEvent(
            'Rate limit exceeded',
            'WARNING'
        );
    }
    
    public static function logInvalidInput(string $field, string $value, string $reason) {
        self::logSecurityEvent(
            'Invalid input detected',
            'WARNING',
            [
                'field' => $field,
                'value' => substr($value, 0, 100), // Limit logged value length
                'reason' => $reason
            ]
        );
    }
    
    public static function logAuthenticationFailure(string $reason) {
        self::logSecurityEvent(
            'Authentication failure',
            'WARNING',
            ['reason' => $reason]
        );
    }
    
    public static function logUnauthorizedAccess(string $resource) {
        self::logSecurityEvent(
            'Unauthorized access attempt',
            'CRITICAL',
            ['resource' => $resource]
        );
    }
    
    public static function logAPIUsage(string $endpoint, int $responseCode, float $responseTime) {
        self::logSecurityEvent(
            'API request',
            'INFO',
            [
                'endpoint' => $endpoint,
                'response_code' => $responseCode,
                'response_time_ms' => round($responseTime * 1000, 2)
            ]
        );
    }
    
    public static function logDatabaseError(string $error) {
        self::logSecurityEvent(
            'Database error',
            'ERROR',
            ['error' => $error]
        );
    }
    
    private static function getClientIP() {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $ip = $_SERVER[$key];
                if (!empty($ip)) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                    
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    public static function generateSecurityReport(int $days = 7) {
        if (!file_exists(self::$logFile)) {
            return ['error' => 'No security log file found'];
        }
        
        $logContent = file_get_contents(self::$logFile);
        $lines = explode(PHP_EOL, trim($logContent));
        
        $events = [];
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $event = json_decode($line, true);
            if ($event && $event['timestamp'] >= $cutoffDate) {
                $events[] = $event;
            }
        }
        
        // Generate statistics
        $stats = [
            'total_events' => count($events),
            'events_by_level' => array_count_values(array_column($events, 'level')),
            'top_ips' => array_count_values(array_column($events, 'client_ip')),
            'top_endpoints' => [],
            'security_incidents' => []
        ];
        
        // Analyze endpoints
        $endpointCounts = [];
        foreach ($events as $event) {
            if (isset($event['context']['endpoint'])) {
                $endpoint = $event['context']['endpoint'];
                $endpointCounts[$endpoint] = ($endpointCounts[$endpoint] ?? 0) + 1;
            }
        }
        arsort($endpointCounts);
        $stats['top_endpoints'] = array_slice($endpointCounts, 0, 10, true);
        
        // Identify security incidents
        foreach ($events as $event) {
            if (in_array($event['level'], ['CRITICAL', 'ERROR'])) {
                $stats['security_incidents'][] = [
                    'timestamp' => $event['timestamp'],
                    'event' => $event['event'],
                    'client_ip' => $event['client_ip'],
                    'level' => $event['level']
                ];
            }
        }
        
        return $stats;
    }
}
