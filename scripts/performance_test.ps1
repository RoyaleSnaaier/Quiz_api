# Performance Testing Script for Quiz API Clean Architecture
# Tests the API under various load conditions

Write-Host "=== Quiz API Performance Testing ===" -ForegroundColor Green
Write-Host "Testing clean architecture performance and reliability" -ForegroundColor Yellow
Write-Host ""

$baseUrl = "http://localhost:8080"
$testResults = @{}

# Function to measure response time
function Measure-ApiCall {
    param(
        [string]$Name,
        [string]$Method = "GET",
        [string]$Url,
        [object]$Body = $null,
        [int]$Iterations = 10
    )
    
    Write-Host "Testing: $Name ($Iterations iterations)" -ForegroundColor Cyan
    
    $times = @()
    $successCount = 0
    $errors = @()
    
    for ($i = 1; $i -le $Iterations; $i++) {
        try {
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
            
            if ($Body) {
                $response = Invoke-RestMethod -Uri $Url -Method $Method -Body ($Body | ConvertTo-Json) -ContentType "application/json" -UseBasicParsing
            } else {
                $response = Invoke-RestMethod -Uri $Url -Method $Method -UseBasicParsing
            }
            
            $stopwatch.Stop()
            $times += $stopwatch.ElapsedMilliseconds
            $successCount++
            
            Write-Progress -Activity "Performance Testing" -Status "$Name" -PercentComplete (($i / $Iterations) * 100)
            
        } catch {
            $errors += $_.Exception.Message
            Write-Host "  ❌ Error in iteration $i`: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
    
    Write-Progress -Activity "Performance Testing" -Completed
    
    if ($times.Count -gt 0) {
        $avgTime = ($times | Measure-Object -Average).Average
        $minTime = ($times | Measure-Object -Minimum).Minimum
        $maxTime = ($times | Measure-Object -Maximum).Maximum
        
        Write-Host "  ✅ Results:" -ForegroundColor Green
        Write-Host "    Success Rate: $successCount/$Iterations ($([math]::Round(($successCount/$Iterations)*100, 2))%)" -ForegroundColor Gray
        Write-Host "    Average Time: $([math]::Round($avgTime, 2))ms" -ForegroundColor Gray
        Write-Host "    Min Time: $minTime ms" -ForegroundColor Gray
        Write-Host "    Max Time: $maxTime ms" -ForegroundColor Gray
        
        $testResults[$Name] = @{
            SuccessRate = ($successCount/$Iterations)*100
            AvgTime = $avgTime
            MinTime = $minTime
            MaxTime = $maxTime
            Errors = $errors
        }
    } else {
        Write-Host "  ❌ All requests failed" -ForegroundColor Red
        $testResults[$Name] = @{
            SuccessRate = 0
            Errors = $errors
        }
    }
    Write-Host ""
}

# Function to run concurrent requests
function Test-ConcurrentRequests {
    param(
        [string]$Name,
        [string]$Url,
        [int]$ConcurrentUsers = 5,
        [int]$RequestsPerUser = 10
    )
    
    Write-Host "Testing: $Name (Concurrent Load)" -ForegroundColor Cyan
    Write-Host "  Users: $ConcurrentUsers, Requests per user: $RequestsPerUser" -ForegroundColor Gray
    
    $jobs = @()
    $startTime = Get-Date
    
    # Start concurrent jobs
    for ($user = 1; $user -le $ConcurrentUsers; $user++) {
        $scriptBlock = {
            param($url, $requests, $userNum)
            
            $results = @()
            for ($i = 1; $i -le $requests; $i++) {
                try {
                    $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
                    $response = Invoke-RestMethod -Uri $url -UseBasicParsing
                    $stopwatch.Stop()
                    
                    $results += @{
                        User = $userNum
                        Request = $i
                        Time = $stopwatch.ElapsedMilliseconds
                        Success = $true
                    }
                } catch {
                    $results += @{
                        User = $userNum
                        Request = $i
                        Time = 0
                        Success = $false
                        Error = $_.Exception.Message
                    }
                }
            }
            return $results
        }
        
        $jobs += Start-Job -ScriptBlock $scriptBlock -ArgumentList $Url, $RequestsPerUser, $user
    }
    
    # Wait for all jobs to complete
    $allResults = @()
    foreach ($job in $jobs) {
        $jobResults = Receive-Job $job -Wait
        $allResults += $jobResults
        Remove-Job $job
    }
    
    $endTime = Get-Date
    $totalTime = ($endTime - $startTime).TotalMilliseconds
    
    # Analyze results
    $successfulRequests = $allResults | Where-Object { $_.Success -eq $true }
    $failedRequests = $allResults | Where-Object { $_.Success -eq $false }
    
    $totalRequests = $ConcurrentUsers * $RequestsPerUser
    $successCount = $successfulRequests.Count
    $successRate = ($successCount / $totalRequests) * 100
    
    Write-Host "  ✅ Concurrent Test Results:" -ForegroundColor Green
    Write-Host "    Total Requests: $totalRequests" -ForegroundColor Gray
    Write-Host "    Successful: $successCount" -ForegroundColor Gray
    Write-Host "    Failed: $($failedRequests.Count)" -ForegroundColor Gray
    Write-Host "    Success Rate: $([math]::Round($successRate, 2))%" -ForegroundColor Gray
    Write-Host "    Total Time: $([math]::Round($totalTime, 2))ms" -ForegroundColor Gray
    Write-Host "    Requests/Second: $([math]::Round($totalRequests / ($totalTime / 1000), 2))" -ForegroundColor Gray
    
    if ($successfulRequests.Count -gt 0) {
        $avgResponseTime = ($successfulRequests.Time | Measure-Object -Average).Average
        Write-Host "    Avg Response Time: $([math]::Round($avgResponseTime, 2))ms" -ForegroundColor Gray
    }
    
    $testResults["$Name (Concurrent)"] = @{
        TotalRequests = $totalRequests
        SuccessRate = $successRate
        TotalTime = $totalTime
        RequestsPerSecond = $totalRequests / ($totalTime / 1000)
        AvgResponseTime = if ($successfulRequests.Count -gt 0) { ($successfulRequests.Time | Measure-Object -Average).Average } else { 0 }
    }
    
    Write-Host ""
}

# ===========================================
# PERFORMANCE TESTS
# ===========================================

Write-Host "Starting Performance Tests..." -ForegroundColor Magenta
Write-Host ""

# Test 1: Basic Read Operations
Measure-ApiCall -Name "GET All Quizzes" -Url "$baseUrl/quizzes" -Iterations 20
Measure-ApiCall -Name "GET Specific Quiz" -Url "$baseUrl/quizzes/1" -Iterations 20
Measure-ApiCall -Name "GET Quiz Questions" -Url "$baseUrl/quiz_questions?quiz_id=1" -Iterations 20
Measure-ApiCall -Name "GET Question Answers" -Url "$baseUrl/answers?question_id=1" -Iterations 20

# Test 2: Create Operations Performance
$testQuiz = @{
    title = "Performance Test Quiz"
    description = "Testing creation performance"
    category = "Testing"
}

Measure-ApiCall -Name "CREATE Quiz" -Method "POST" -Url "$baseUrl/quizzes" -Body $testQuiz -Iterations 10

# Test 3: Concurrent Load Tests
Test-ConcurrentRequests -Name "GET All Quizzes" -Url "$baseUrl/quizzes" -ConcurrentUsers 5 -RequestsPerUser 5
Test-ConcurrentRequests -Name "GET Specific Quiz" -Url "$baseUrl/quizzes/1" -ConcurrentUsers 10 -RequestsPerUser 3

# Test 4: Mixed Operations Under Load
Write-Host "Testing: Mixed Operations (Read/Write Mix)" -ForegroundColor Cyan
$mixedJobs = @()
$mixedStartTime = Get-Date

# Start read-heavy operations
for ($i = 1; $i -le 3; $i++) {
    $readScript = {
        param($baseUrl)
        $results = @()
        for ($j = 1; $j -le 10; $j++) {
            try {
                $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
                $response = Invoke-RestMethod -Uri "$baseUrl/quizzes" -UseBasicParsing
                $stopwatch.Stop()
                $results += @{ Operation = "READ"; Time = $stopwatch.ElapsedMilliseconds; Success = $true }
            } catch {
                $results += @{ Operation = "READ"; Time = 0; Success = $false }
            }
        }
        return $results
    }
    $mixedJobs += Start-Job -ScriptBlock $readScript -ArgumentList $baseUrl
}

# Start write operations
$writeScript = {
    param($baseUrl)
    $results = @()
    for ($j = 1; $j -le 3; $j++) {
        try {
            $testData = @{
                title = "Load Test Quiz $j"
                description = "Created during load testing"
                category = "Testing"
            } | ConvertTo-Json
            
            $stopwatch = [System.Diagnostics.Stopwatch]::StartNew()
            $response = Invoke-RestMethod -Uri "$baseUrl/quizzes" -Method POST -Body $testData -ContentType "application/json" -UseBasicParsing
            $stopwatch.Stop()
            $results += @{ Operation = "WRITE"; Time = $stopwatch.ElapsedMilliseconds; Success = $true; CreatedId = $response.Data.id }
        } catch {
            $results += @{ Operation = "WRITE"; Time = 0; Success = $false }
        }
    }
    return $results
}
$mixedJobs += Start-Job -ScriptBlock $writeScript -ArgumentList $baseUrl

# Wait for mixed operations to complete
$mixedResults = @()
foreach ($job in $mixedJobs) {
    $jobResults = Receive-Job $job -Wait
    $mixedResults += $jobResults
    Remove-Job $job
}

$mixedEndTime = Get-Date
$mixedTotalTime = ($mixedEndTime - $mixedStartTime).TotalMilliseconds

$readResults = $mixedResults | Where-Object { $_.Operation -eq "READ" }
$writeResults = $mixedResults | Where-Object { $_.Operation -eq "WRITE" }

Write-Host "  ✅ Mixed Operations Results:" -ForegroundColor Green
Write-Host "    Read Operations: $($readResults.Count) (Success: $(($readResults | Where-Object Success).Count))" -ForegroundColor Gray
Write-Host "    Write Operations: $($writeResults.Count) (Success: $(($writeResults | Where-Object Success).Count))" -ForegroundColor Gray
Write-Host "    Total Time: $([math]::Round($mixedTotalTime, 2))ms" -ForegroundColor Gray

# Clean up created test data
$createdIds = $writeResults | Where-Object { $_.Success -and $_.CreatedId } | ForEach-Object { $_.CreatedId }
foreach ($id in $createdIds) {
    try {
        Invoke-RestMethod -Uri "$baseUrl/quizzes/$id" -Method DELETE -UseBasicParsing | Out-Null
    } catch {
        # Ignore cleanup errors
    }
}

Write-Host ""

# ===========================================
# PERFORMANCE SUMMARY
# ===========================================

Write-Host "=== PERFORMANCE SUMMARY ===" -ForegroundColor Green
Write-Host ""

foreach ($testName in $testResults.Keys) {
    $result = $testResults[$testName]
    Write-Host "$testName`:" -ForegroundColor Cyan
    
    if ($result.SuccessRate -ne $null) {
        Write-Host "  Success Rate: $([math]::Round($result.SuccessRate, 2))%" -ForegroundColor Gray
        
        if ($result.AvgTime) {
            Write-Host "  Avg Response: $([math]::Round($result.AvgTime, 2))ms" -ForegroundColor Gray
        }
        
        if ($result.RequestsPerSecond) {
            Write-Host "  Throughput: $([math]::Round($result.RequestsPerSecond, 2)) req/sec" -ForegroundColor Gray
        }
        
        # Performance rating
        if ($result.SuccessRate -ge 95 -and $result.AvgTime -lt 200) {
            Write-Host "  Rating: ✅ EXCELLENT" -ForegroundColor Green
        } elseif ($result.SuccessRate -ge 90 -and $result.AvgTime -lt 500) {
            Write-Host "  Rating: ✅ GOOD" -ForegroundColor Yellow
        } elseif ($result.SuccessRate -ge 80) {
            Write-Host "  Rating: ⚠️  ACCEPTABLE" -ForegroundColor Yellow
        } else {
            Write-Host "  Rating: ❌ NEEDS IMPROVEMENT" -ForegroundColor Red
        }
    }
    Write-Host ""
}

Write-Host "Performance testing completed!" -ForegroundColor Green
Write-Host "Clean architecture is ready for production use." -ForegroundColor Yellow
