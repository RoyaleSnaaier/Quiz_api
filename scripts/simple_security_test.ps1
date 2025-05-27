# Simple Security Validation Test
param(
    [string]$BaseUrl = "http://localhost:8080"
)

Write-Host "=== Quiz API Security Validation ===" -ForegroundColor Cyan
Write-Host "Target: $BaseUrl" -ForegroundColor Yellow
Write-Host ""

$passed = 0
$failed = 0

# Test 1: Security Headers
Write-Host "1. Testing Security Headers..." -ForegroundColor Green
try {
    $response = Invoke-WebRequest -Uri "$BaseUrl/quizzes" -Method Get -ErrorAction Stop
    
    if ($response.Headers["X-Content-Type-Options"] -eq "nosniff") {
        Write-Host "  + X-Content-Type-Options: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - X-Content-Type-Options: FAIL" -ForegroundColor Red
        $failed++
    }
    
    if ($response.Headers["X-Frame-Options"] -eq "DENY") {
        Write-Host "  + X-Frame-Options: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - X-Frame-Options: FAIL" -ForegroundColor Red
        $failed++
    }
    
    if ($response.Headers["X-XSS-Protection"]) {
        Write-Host "  + X-XSS-Protection: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - X-XSS-Protection: FAIL" -ForegroundColor Red
        $failed++
    }
    
} catch {
    Write-Host "  - Security headers test failed: $($_.Exception.Message)" -ForegroundColor Red
    $failed++
}

# Test 2: Input Validation
Write-Host "2. Testing Input Validation..." -ForegroundColor Green

# Test empty title
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/quizzes" -Method POST -Body '{"title":"","description":"Test"}' -ContentType "application/json" -ErrorAction SilentlyContinue
    Write-Host "  - Empty title validation: FAIL (should reject)" -ForegroundColor Red
    $failed++
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 400) {
        Write-Host "  + Empty title validation: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - Empty title validation: FAIL (wrong status code)" -ForegroundColor Red
        $failed++
    }
}

# Test valid input
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/quizzes" -Method POST -Body '{"title":"Security Test","description":"Valid test"}' -ContentType "application/json" -ErrorAction Stop
    Write-Host "  + Valid input acceptance: PASS" -ForegroundColor Green
    $passed++
    
    # Clean up
    if ($response.data.id) {
        try {
            Invoke-RestMethod -Uri "$BaseUrl/quizzes/$($response.data.id)" -Method DELETE -ErrorAction SilentlyContinue
        } catch { }
    }
    
} catch {
    Write-Host "  - Valid input acceptance: FAIL" -ForegroundColor Red
    $failed++
}

# Test 3: Error Handling
Write-Host "3. Testing Error Handling..." -ForegroundColor Green
try {
    $response = Invoke-RestMethod -Uri "$BaseUrl/nonexistent" -Method Get -ErrorAction SilentlyContinue
    Write-Host "  - Error handling: FAIL (should return error)" -ForegroundColor Red
    $failed++
} catch {
    $errorMessage = $_.Exception.Message
    if ($errorMessage -notmatch "password|database|connection|file_get_contents") {
        Write-Host "  + Error message sanitization: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - Error message sanitization: FAIL (leaks info)" -ForegroundColor Red
        $failed++
    }
}

# Test 4: Security Logging
Write-Host "4. Testing Security Logging..." -ForegroundColor Green
$logFile = "logs\security.log"
if (Test-Path $logFile) {
    $logContent = Get-Content $logFile -ErrorAction SilentlyContinue
    if ($logContent.Count -gt 0) {
        Write-Host "  + Security logging: PASS (log file exists with entries)" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - Security logging: FAIL (log file empty)" -ForegroundColor Red
        $failed++
    }
} else {
    Write-Host "  - Security logging: FAIL (log file not found)" -ForegroundColor Red
    $failed++
}

# Test 5: Rate Limiting Headers
Write-Host "5. Testing Rate Limiting..." -ForegroundColor Green
try {
    $response = Invoke-WebRequest -Uri "$BaseUrl/quizzes" -Method Get -ErrorAction Stop
    
    if ($response.Headers["X-RateLimit-Limit"]) {
        Write-Host "  + Rate limiting headers: PASS" -ForegroundColor Green
        $passed++
    } else {
        Write-Host "  - Rate limiting headers: FAIL (not present)" -ForegroundColor Red
        $failed++
    }
} catch {
    Write-Host "  - Rate limiting test: FAIL" -ForegroundColor Red
    $failed++
}

# Calculate score
$total = $passed + $failed
$score = if ($total -gt 0) { [math]::Round(($passed / $total) * 100, 1) } else { 0 }

Write-Host ""
Write-Host "=== RESULTS ===" -ForegroundColor Cyan
Write-Host "Passed: $passed" -ForegroundColor Green
Write-Host "Failed: $failed" -ForegroundColor Red
Write-Host "Score: $score%" -ForegroundColor $(if ($score -ge 80) { "Green" } else { "Yellow" })

Write-Host ""
if ($score -ge 80) {
    Write-Host "PRODUCTION READY: Security implementation meets requirements!" -ForegroundColor Green
} else {
    Write-Host "NEEDS WORK: Address failed tests before production deployment" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Validation completed at $(Get-Date)" -ForegroundColor Gray
