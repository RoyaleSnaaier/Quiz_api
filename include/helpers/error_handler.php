<?php
// Error handling and logging
class ErrorHandler {
    
    public static function register() {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $errorTypes = [
            E_ERROR             => 'Fatal Error',
            E_WARNING           => 'Warning',
            E_PARSE             => 'Parse Error',
            E_NOTICE            => 'Notice',
            E_CORE_ERROR        => 'Core Error',
            E_CORE_WARNING      => 'Core Warning',
            E_COMPILE_ERROR     => 'Compile Error',
            E_COMPILE_WARNING   => 'Compile Warning',
            E_USER_ERROR        => 'User Error',
            E_USER_WARNING      => 'User Warning',
            E_USER_NOTICE       => 'User Notice',
            E_STRICT            => 'Runtime Notice'
        ];
        
        $errorType = $errorTypes[$severity] ?? 'Unknown Error';
        
        self::logError($errorType, $message, $file, $line);
        
        if (in_array($severity, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            new Response('Internal Server Error', 'An error occurred while processing your request', 500);
            exit;
        }
        
        return true;
    }
    
    public static function handleException($exception) {
        self::logError(
            'Uncaught Exception',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
        
        new Response('Internal Server Error', 'An unexpected error occurred', 500);
        exit;
    }
    
    public static function handleShutdown() {
        $error = error_get_last();
        
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            self::logError('Fatal Error', $error['message'], $error['file'], $error['line']);
            new Response('Internal Server Error', 'A fatal error occurred', 500);
        }
    }
    
    private static function logError($type, $message, $file, $line, $trace = null) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$type}: {$message} in {$file} on line {$line}";
        
        if ($trace) {
            $logMessage .= "\nStack trace:\n{$trace}";
        }
        
        error_log($logMessage);
        
        // In development, also log to a file
        if (Config::get('api')['debug'] ?? false) {
            $logFile = __DIR__ . '/../logs/error.log';
            
            // Create logs directory if it doesn't exist
            $logDir = dirname($logFile);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            file_put_contents($logFile, $logMessage . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}
