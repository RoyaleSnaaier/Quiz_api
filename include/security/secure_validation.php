<?php
require_once __DIR__ . '/security_logger.php';

// Enhanced validation with security monitoring
class SecureValidationHelper {
    
    // Known malicious patterns
    private static $sqlInjectionPatterns = [
        '/(\bUNION\b|\bSELECT\b|\bINSERT\b|\bUPDATE\b|\bDELETE\b|\bDROP\b|\bCREATE\b|\bALTER\b)/i',
        '/(\b--\b|\b#\b|\/\*|\*\/)/i',
        '/(\bOR\b|\bAND\b)\s+[\'"`]?[^\'"`]*[\'"`]?\s*=\s*[\'"`]?[^\'"`]*[\'"`]?/i',
        '/(\'\s*(OR|AND)\s*\'\s*=\s*\'|\"\s*(OR|AND)\s*\"\s*=\s*\")/i'
    ];
    
    private static $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/<iframe[^>]*>.*?<\/iframe>/is',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<[^>]*\s+(on\w+|href|src)\s*=/i',
        '/vbscript:/i',
        '/data:text\/html/i'
    ];
    
    public static function validateAndSanitize(array $data, array $rules): array {
        $sanitized = [];
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            if (!isset($data[$field])) {
                if ($rule['required'] ?? false) {
                    $errors[] = "Field '{$field}' is required";
                }
                continue;
            }
            
            $value = $data[$field];
            
            try {
                // Check for security threats
                self::checkForSecurityThreats($field, $value);
                
                // Validate and sanitize based on type
                switch ($rule['type']) {
                    case 'string':
                        $sanitized[$field] = self::validateString(
                            $value, 
                            $rule['max_length'] ?? null,
                            $rule['min_length'] ?? null,
                            $field
                        );
                        break;
                        
                    case 'int':
                        $sanitized[$field] = self::validateInteger(
                            $value,
                            $rule['min'] ?? null,
                            $rule['max'] ?? null,
                            $field
                        );
                        break;
                        
                    case 'bool':
                        $sanitized[$field] = self::validateBoolean($value, $field);
                        break;
                        
                    case 'url':
                        $sanitized[$field] = self::validateUrl($value, $field);
                        break;
                        
                    case 'email':
                        $sanitized[$field] = self::validateEmail($value, $field);
                        break;
                        
                    default:
                        throw new InvalidArgumentException("Unknown validation type: {$rule['type']}");
                }
                
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
                SecurityLogger::logInvalidInput($field, $value, $e->getMessage());
            }
        }
        
        if (!empty($errors)) {
            throw new InvalidArgumentException(implode(', ', $errors));
        }
        
        return $sanitized;
    }
    
    private static function checkForSecurityThreats(string $field, $value) {
        if (!is_string($value)) return;
        
        // Check for SQL injection patterns
        foreach (self::$sqlInjectionPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                SecurityLogger::logSQLInjectionAttempt($value);
                throw new SecurityException("Potential SQL injection detected in field '{$field}'");
            }
        }
        
        // Check for XSS patterns
        foreach (self::$xssPatterns as $pattern) {
            if (preg_match($pattern, $value)) {
                SecurityLogger::logXSSAttempt($value);
                throw new SecurityException("Potential XSS attempt detected in field '{$field}'");
            }
        }
        
        // Check for path traversal
        if (strpos($value, '../') !== false || strpos($value, '..\\') !== false) {
            SecurityLogger::logSecurityEvent('Path traversal attempt', 'CRITICAL', ['field' => $field, 'value' => $value]);
            throw new SecurityException("Path traversal attempt detected in field '{$field}'");
        }
    }
    
    private static function validateString($value, ?int $maxLength, ?int $minLength, string $field): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException("Field '{$field}' must be a string");
        }
        
        $value = trim($value);
        
        if ($minLength && strlen($value) < $minLength) {
            throw new InvalidArgumentException("Field '{$field}' must be at least {$minLength} characters");
        }
        
        if ($maxLength && strlen($value) > $maxLength) {
            throw new InvalidArgumentException("Field '{$field}' cannot exceed {$maxLength} characters");
        }
        
        // Sanitize HTML entities
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    private static function validateInteger($value, ?int $min, ?int $max, string $field): int {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Field '{$field}' must be a number");
        }
        
        $intValue = (int)$value;
        
        if ($min !== null && $intValue < $min) {
            throw new InvalidArgumentException("Field '{$field}' must be at least {$min}");
        }
        
        if ($max !== null && $intValue > $max) {
            throw new InvalidArgumentException("Field '{$field}' cannot exceed {$max}");
        }
        
        return $intValue;
    }
    
    private static function validateBoolean($value, string $field): bool {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $lower = strtolower($value);
            if (in_array($lower, ['true', '1', 'yes', 'on'])) {
                return true;
            }
            if (in_array($lower, ['false', '0', 'no', 'off'])) {
                return false;
            }
        }
        
        if (is_numeric($value)) {
            return (bool)(int)$value;
        }
        
        throw new InvalidArgumentException("Field '{$field}' must be a boolean value");
    }
    
    private static function validateUrl($value, string $field): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException("Field '{$field}' must be a string");
        }
        
        $value = trim($value);
        
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException("Field '{$field}' must be a valid URL");
        }
        
        // Check for dangerous URL schemes
        $scheme = parse_url($value, PHP_URL_SCHEME);
        $allowedSchemes = ['http', 'https'];
        
        if (!in_array(strtolower($scheme), $allowedSchemes)) {
            throw new InvalidArgumentException("Field '{$field}' must use http or https scheme");
        }
        
        return $value;
    }
    
    private static function validateEmail($value, string $field): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException("Field '{$field}' must be a string");
        }
        
        $value = trim($value);
        
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Field '{$field}' must be a valid email address");
        }
        
        return $value;
    }
    
    public static function validateRequired(array $data, array $requiredFields): array {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || 
                (is_string($data[$field]) && empty(trim($data[$field]))) ||
                (is_array($data[$field]) && empty($data[$field])) ||
                $data[$field] === null) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        return $errors;
    }
}

// Custom exceptions for security
class SecurityException extends Exception {}
class ValidationException extends Exception {}
