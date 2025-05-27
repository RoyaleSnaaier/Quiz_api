<?php
// Input validation and sanitization helper
class ValidationHelper {
    
    public static function validateRequired(array $data, array $requiredFields): array {
        $errors = [];
        
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = "Field '{$field}' is required";
            }
        }
        
        return $errors;
    }
    
    public static function sanitizeString(?string $value, int $maxLength = null): ?string {
        if ($value === null) return null;
        
        $sanitized = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
        
        if ($maxLength && strlen($sanitized) > $maxLength) {
            throw new InvalidArgumentException("Value exceeds maximum length of {$maxLength} characters");
        }
        
        return $sanitized;
    }
    
    public static function validateEmail(?string $email): bool {
        return $email ? filter_var($email, FILTER_VALIDATE_EMAIL) !== false : true;
    }
    
    public static function validateUrl(?string $url): bool {
        return $url ? filter_var($url, FILTER_VALIDATE_URL) !== false : true;
    }
    
    public static function validateInteger($value, int $min = null, int $max = null): bool {
        if (!is_numeric($value)) return false;
        
        $intValue = (int)$value;
        
        if ($min !== null && $intValue < $min) return false;
        if ($max !== null && $intValue > $max) return false;
        
        return true;
    }
    
    public static function sanitizeArray(array $data, array $rules): array {
        $sanitized = [];
        
        foreach ($rules as $field => $rule) {
            if (isset($data[$field])) {
                switch ($rule['type']) {
                    case 'string':
                        $sanitized[$field] = self::sanitizeString(
                            $data[$field], 
                            $rule['max_length'] ?? null
                        );
                        break;
                    case 'int':
                        $sanitized[$field] = (int)$data[$field];
                        break;
                    case 'bool':
                        $sanitized[$field] = (bool)$data[$field];
                        break;
                    case 'url':
                        if (!self::validateUrl($data[$field])) {
                            throw new InvalidArgumentException("Invalid URL for field '{$field}'");
                        }
                        $sanitized[$field] = $data[$field];
                        break;
                }
            }
        }
          return $sanitized;
    }
    
    public static function validateMaxLength(array $data, array $limits): array {
        $errors = [];
        
        foreach ($limits as $field => $maxLength) {
            if (isset($data[$field]) && strlen($data[$field]) > $maxLength) {
                $errors[] = "Field '{$field}' exceeds maximum length of {$maxLength} characters";
            }
        }
        
        return $errors;
    }
    
    public static function sanitizeUrl(?string $url): ?string {
        if ($url === null) return null;
        
        if (!self::validateUrl($url)) {
            throw new InvalidArgumentException("Invalid URL format");
        }
        
        return $url;
    }
}
