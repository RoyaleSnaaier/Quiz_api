# Quiz API Security Audit Script
# Comprehensive security assessment for production readiness

param(
    [string]$BaseUrl = "http://localhost:8080",
    [switch]$Verbose = $false
)

Write-Host "=== Quiz API Security Audit ===" -ForegroundColor Cyan
Write-Host "Target: $BaseUrl" -ForegroundColor Yellow
Write-Host "Timestamp: $(Get-Date)" -ForegroundColor Gray
Write-Host ""

$auditResults = @{
    "Passed" = @()
    "Failed" = @()
    "Warnings" = @()
    "Info" = @()
}

function Test-SecurityHeader {
    param($Response, $HeaderName, $ExpectedValue = $null)
    
    $header = $Response.Headers[$HeaderName]
    if ($header) {
        if ($ExpectedValue -and $header -ne $ExpectedValue) {
            return @{ Status = "Failed"; Message = "$HeaderName header found but incorrect value: $header" }
        }
        return @{ Status = "Passed"; Message = "$HeaderName header properly set: $header" }
    } else {
        return @{ Status = "Failed"; Message = "$HeaderName header missing" }
    }
}

function Test-InputValidation {
    param($Endpoint, $Method, $InvalidData)
    
    try {
        $response = Invoke-RestMethod -Uri "$BaseUrl$Endpoint" -Method $Method -Body ($InvalidData | ConvertTo-Json) -ContentType "application/json" -ErrorAction SilentlyContinue
        return @{ Status = "Failed"; Message = "Accepted invalid input: $($InvalidData | ConvertTo-Json -Compress)" }
    } catch {
        if ($_.Exception.Response.StatusCode -eq 400) {
            return @{ Status = "Passed"; Message = "Properly rejected invalid input with 400 status" }
        } else {
            return @{ Status = "Warning"; Message = "Rejected input but with status: $($_.Exception.Response.StatusCode)" }
        }
    }
}

function Test-SqlInjection {
    param($Endpoint)
    
    $sqlPayloads = @(
        "'; DROP TABLE quizes; --",
        "1' OR '1'='1",
        "1 UNION SELECT * FROM information_schema.tables",
        "'; INSERT INTO quizes (title) VALUES ('malicious'); --"
    )
    
    $vulnerabilities = @()
    
    foreach ($payload in $sqlPayloads) {
        try {
            $testUrl = "$BaseUrl$Endpoint" + "?id=$payload"
            $response = Invoke-RestMethod -Uri $testUrl -Method Get -ErrorAction SilentlyContinue
            
            # Check if response suggests SQL injection worked
            if ($response -match "information_schema|malicious|syntax error") {
                $vulnerabilities += "Potential SQL injection with payload: $payload"
            }
        } catch {
            # Errors are expected and good - means the injection was blocked
        }
    }
    
    if ($vulnerabilities.Count -eq 0) {
        return @{ Status = "Passed"; Message = "No SQL injection vulnerabilities detected" }
    } else {
        return @{ Status = "Failed"; Message = "Potential SQL injection vulnerabilities: $($vulnerabilities -join ', ')" }
    }
}

function Test-XSSPrevention {
    param($Endpoint, $Method)
    
    $xssPayloads = @(
        "<script>alert('xss')</script>",
        "<img src=x onerror=alert('xss')>",
        "javascript:alert('xss')",
        "'; alert('xss'); //"
    )
    
    $vulnerabilities = @()
    
    foreach ($payload in $xssPayloads) {
        try {
            $testData = @{
                title = $payload
                description = "Test description"
            }
            
            $response = Invoke-RestMethod -Uri "$BaseUrl$Endpoint" -Method $Method -Body ($testData | ConvertTo-Json) -ContentType "application/json" -ErrorAction SilentlyContinue
            
            # Check if the response contains unescaped script tags
            if ($response -match "<script|javascript:|onerror=") {
                $vulnerabilities += "XSS payload not properly sanitized: $payload"
            }
        } catch {
            # Could be good (validation rejected) or bad (server error)
            if ($_.Exception.Response.StatusCode -ne 400) {
                $vulnerabilities += "Unexpected error with XSS payload: $payload"
            }
        }
    }
    
    if ($vulnerabilities.Count -eq 0) {
        return @{ Status = "Passed"; Message = "XSS prevention appears effective" }
    } else {
        return @{ Status = "Failed"; Message = "Potential XSS vulnerabilities: $($vulnerabilities -join ', ')" }
    }
}

function Test-AuthenticationBypasses {
    # Test for authentication bypass attempts
    $bypassAttempts = @(
        @{ Header = "X-Forwarded-For"; Value = "127.0.0.1" },
        @{ Header = "X-Real-IP"; Value = "127.0.0.1" },
        @{ Header = "X-Original-URL"; Value = "/admin" },
        @{ Header = "X-Rewrite-URL"; Value = "/admin" }
    )
    
    $issues = @()
    
    foreach ($attempt in $bypassAttempts) {
        try {
            $headers = @{ $attempt.Header = $attempt.Value }
            $response = Invoke-RestMethod -Uri "$BaseUrl/quizzes" -Headers $headers -ErrorAction SilentlyContinue
            # If no errors, it might be fine, but we should note it
        } catch {
            # Expected behavior
        }
    }
    
    return @{ Status = "Info"; Message = "Authentication bypass tests completed (API appears to be public)" }
}

function Test-ErrorInformationLeakage {
    try {
        # Test invalid endpoint
        $response = Invoke-RestMethod -Uri "$BaseUrl/invalid_endpoint_test" -ErrorAction SilentlyContinue
    } catch {
        $errorMessage = $_.Exception.Message
        
        # Check for information leakage in error messages
        if ($errorMessage -match "file_get_contents|fopen|mysql|database|connection|password|username") {
            return @{ Status = "Failed"; Message = "Error messages may leak sensitive information: $errorMessage" }
        } else {
            return @{ Status = "Passed"; Message = "Error messages appear to be properly sanitized" }
        }
    }
    
    return @{ Status = "Info"; Message = "Error handling test completed" }
}

function Test-RateLimiting {
    Write-Host "Testing rate limiting (making 20 rapid requests)..." -ForegroundColor Yellow
    
    $requests = 1..20 | ForEach-Object {
        Start-Job -ScriptBlock {
            param($url)
            try {
                $start = Get-Date
                $response = Invoke-RestMethod -Uri $url -Method Get -ErrorAction Stop
                $end = Get-Date
                return @{
                    Success = $true
                    StatusCode = 200
                    ResponseTime = ($end - $start).TotalMilliseconds
                }
            } catch {
                return @{
                    Success = $false
                    StatusCode = $_.Exception.Response.StatusCode.value__
                    Error = $_.Exception.Message
                }
            }
        } -ArgumentList "$BaseUrl/quizzes"
    }
    
    $results = $requests | Wait-Job | Receive-Job
    $requests | Remove-Job
    
    $failedRequests = $results | Where-Object { -not $_.Success -and $_.StatusCode -eq 429 }
    
    if ($failedRequests.Count -gt 0) {
        return @{ Status = "Passed"; Message = "Rate limiting detected - $($failedRequests.Count) requests were rate limited" }
    } else {
        return @{ Status = "Warning"; Message = "No rate limiting detected - all 20 rapid requests succeeded" }
    }
}

function Test-HTTPSRedirection {
    if ($BaseUrl -match "^https://") {
        return @{ Status = "Passed"; Message = "Using HTTPS connection" }
    } else {
        try {
            $httpsUrl = $BaseUrl -replace "^http://", "https://"
            $response = Invoke-WebRequest -Uri $httpsUrl -Method Head -ErrorAction Stop
            return @{ Status = "Warning"; Message = "HTTPS available but not being used in tests" }
        } catch {
            return @{ Status = "Failed"; Message = "HTTPS not available - production should use HTTPS" }
        }
    }
}

function Test-DeprecationHeaders {
    try {
        $response = Invoke-WebRequest -Uri "$BaseUrl/quizzes-legacy" -Method Get -ErrorAction Stop
        
        $deprecationHeader = $response.Headers['X-API-Deprecation-Warning']
        $migrationHeader = $response.Headers['X-API-Migration-Guide']
        
        if ($deprecationHeader) {
            return @{ Status = "Passed"; Message = "Deprecation headers properly implemented" }
        } else {
            return @{ Status = "Failed"; Message = "Legacy endpoints missing deprecation headers" }
        }
    } catch {
        return @{ Status = "Info"; Message = "Could not test legacy endpoints" }
    }
}

# ======================
# EXECUTE SECURITY TESTS
# ======================

Write-Host "1. Testing Security Headers..." -ForegroundColor Green
try {
    $response = Invoke-WebRequest -Uri "$BaseUrl/quizzes" -Method Get -ErrorAction Stop
    
    # Test Content-Type header
    $result = Test-SecurityHeader -Response $response -HeaderName "Content-Type" -ExpectedValue "application/json"
    $auditResults[$result.Status] += $result.Message
    
    # Check for other security headers (these might not be implemented yet)
    $securityHeaders = @(
        "X-Content-Type-Options",
        "X-Frame-Options", 
        "X-XSS-Protection",
        "Strict-Transport-Security"
    )
    
    foreach ($header in $securityHeaders) {
        $result = Test-SecurityHeader -Response $response -HeaderName $header
        $auditResults[$result.Status] += $result.Message
    }
    
} catch {
    $auditResults["Failed"] += "Could not connect to API for header testing: $($_.Exception.Message)"
}

Write-Host "2. Testing Input Validation..." -ForegroundColor Green

# Test invalid data types
$invalidInputs = @(
    @{ title = ""; description = "Test" },  # Empty required field
    @{ title = "A" * 300; description = "Test" },  # Exceeds length limit
    @{ title = 123; description = "Test" },  # Wrong data type
    @{ title = $null; description = "Test" }  # Null required field
)

foreach ($input in $invalidInputs) {
    $result = Test-InputValidation -Endpoint "/quizzes" -Method "POST" -InvalidData $input
    $auditResults[$result.Status] += $result.Message
}

Write-Host "3. Testing SQL Injection Protection..." -ForegroundColor Green
$result = Test-SqlInjection -Endpoint "/quizzes"
$auditResults[$result.Status] += $result.Message

Write-Host "4. Testing XSS Prevention..." -ForegroundColor Green
$result = Test-XSSPrevention -Endpoint "/quizzes" -Method "POST"
$auditResults[$result.Status] += $result.Message

Write-Host "5. Testing Authentication Bypasses..." -ForegroundColor Green
$result = Test-AuthenticationBypasses
$auditResults[$result.Status] += $result.Message

Write-Host "6. Testing Error Information Leakage..." -ForegroundColor Green
$result = Test-ErrorInformationLeakage
$auditResults[$result.Status] += $result.Message

Write-Host "7. Testing Rate Limiting..." -ForegroundColor Green
$result = Test-RateLimiting
$auditResults[$result.Status] += $result.Message

Write-Host "8. Testing HTTPS Configuration..." -ForegroundColor Green
$result = Test-HTTPSRedirection
$auditResults[$result.Status] += $result.Message

Write-Host "9. Testing Deprecation Headers..." -ForegroundColor Green
$result = Test-DeprecationHeaders
$auditResults[$result.Status] += $result.Message

# ======================
# REPORT RESULTS
# ======================

Write-Host ""
Write-Host "=== SECURITY AUDIT RESULTS ===" -ForegroundColor Cyan

Write-Host ""
Write-Host "PASSED CHECKS ($($auditResults.Passed.Count)):" -ForegroundColor Green
$auditResults.Passed | ForEach-Object { Write-Host "  + $_" -ForegroundColor Green }

Write-Host ""
Write-Host "WARNINGS ($($auditResults.Warnings.Count)):" -ForegroundColor Yellow
$auditResults.Warnings | ForEach-Object { Write-Host "  ! $_" -ForegroundColor Yellow }

Write-Host ""
Write-Host "FAILED CHECKS ($($auditResults.Failed.Count)):" -ForegroundColor Red
$auditResults.Failed | ForEach-Object { Write-Host "  - $_" -ForegroundColor Red }

Write-Host ""
Write-Host "INFORMATION ($($auditResults.Info.Count)):" -ForegroundColor Blue
$auditResults.Info | ForEach-Object { Write-Host "  i $_" -ForegroundColor Blue }

# Calculate security score
$totalTests = $auditResults.Passed.Count + $auditResults.Failed.Count + $auditResults.Warnings.Count
$securityScore = if ($totalTests -gt 0) { 
    [math]::Round(($auditResults.Passed.Count / $totalTests) * 100, 1) 
} else { 0 }

Write-Host ""
Write-Host "=== SECURITY SCORE: $securityScore% ===" -ForegroundColor $(
    if ($securityScore -ge 80) { "Green" }
    elseif ($securityScore -ge 60) { "Yellow" }
    else { "Red" }
)

Write-Host ""
Write-Host "=== RECOMMENDATIONS ===" -ForegroundColor Cyan

if ($auditResults.Failed.Count -gt 0) {
    Write-Host "CRITICAL: Address all failed security checks before production deployment" -ForegroundColor Red
}

if ($auditResults.Warnings.Count -gt 0) {
    Write-Host "IMPORTANT: Review and address warning items for enhanced security" -ForegroundColor Yellow
}

# Specific recommendations
$recommendations = @(
    "Implement HTTPS for production deployment",
    "Add security headers (X-Content-Type-Options, X-Frame-Options, etc.)",
    "Consider implementing rate limiting for production",
    "Implement comprehensive logging for security monitoring",
    "Regular security scans and penetration testing",
    "Implement API authentication if needed for production",
    "Regular security updates for dependencies",
    "Use a Web Application Firewall (WAF) in production"
)

Write-Host ""
$recommendations | ForEach-Object { Write-Host $_ -ForegroundColor Cyan }

Write-Host ""
Write-Host "Audit completed at $(Get-Date)" -ForegroundColor Gray
