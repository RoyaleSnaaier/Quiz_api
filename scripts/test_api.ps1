# Quiz API Testing Script - Clean Architecture (Primary Endpoints)
# Run this script to test all API endpoints using the primary clean architecture implementation

Write-Host "=== Quiz API Testing Script (Clean Architecture) ===" -ForegroundColor Green
Write-Host "Testing primary endpoints using clean architecture implementation" -ForegroundColor Yellow
Write-Host "Version: 2.0.0 - Clean Architecture Production" -ForegroundColor Cyan
Write-Host ""

$baseUrl = "http://localhost:8080"

# Test 1: API Documentation
Write-Host "1. Testing API Documentation..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl"
    Write-Host "✅ API Documentation loaded successfully" -ForegroundColor Green
    Write-Host "   API Name: $($response.name)" -ForegroundColor Gray
    Write-Host "   Version: $($response.version)" -ForegroundColor Gray
    Write-Host ""
} catch {
    Write-Host "❌ Failed to load API documentation: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 2: Database Health
Write-Host "2. Testing Database Health..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/db_health"
    Write-Host "✅ Database health check passed" -ForegroundColor Green
    Write-Host "   Status: $($response.message)" -ForegroundColor Gray
    Write-Host ""
} catch {
    Write-Host "❌ Database health check failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 3: Get All Quizzes
Write-Host "3. Testing Get All Quizzes..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/quizzes"
    $quizCount = $response.Data.Count
    Write-Host "✅ Retrieved $quizCount quizzes successfully" -ForegroundColor Green
    foreach ($quiz in $response.Data) {
        Write-Host "   - $($quiz.title) (Category: $($quiz.category))" -ForegroundColor Gray
    }
    Write-Host ""
} catch {
    Write-Host "❌ Failed to retrieve quizzes: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 4: Get Specific Quiz
Write-Host "4. Testing Get Specific Quiz (ID: 1)..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/quizzes/1"
    Write-Host "✅ Retrieved quiz successfully" -ForegroundColor Green
    Write-Host "   Title: $($response.Data.title)" -ForegroundColor Gray
    Write-Host "   Description: $($response.Data.description)" -ForegroundColor Gray
    Write-Host ""
} catch {
    Write-Host "❌ Failed to retrieve specific quiz: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 5: Filter Quizzes by Category
Write-Host "5. Testing Category Filter (Science)..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/quizzes?category=Science"
    $quizCount = $response.Data.Count
    Write-Host "✅ Retrieved $quizCount Science quizzes" -ForegroundColor Green
    foreach ($quiz in $response.Data) {
        Write-Host "   - $($quiz.title)" -ForegroundColor Gray
    }
    Write-Host ""
} catch {
    Write-Host "❌ Failed to filter quizzes by category: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 6: Get Quiz Questions
Write-Host "6. Testing Get Quiz Questions (Quiz ID: 1)..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/quiz_questions?quiz_id=1"
    $questionCount = $response.Data.Count
    Write-Host "✅ Retrieved $questionCount questions for quiz 1" -ForegroundColor Green
    foreach ($question in $response.Data) {
        Write-Host "   - $($question.question_text)" -ForegroundColor Gray
    }
    Write-Host ""
} catch {
    Write-Host "❌ Failed to retrieve quiz questions: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 7: Get Question Answers
Write-Host "7. Testing Get Question Answers (Question ID: 1)..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/answers?question_id=1"
    $answerCount = $response.Data.Count
    Write-Host "✅ Retrieved $answerCount answers for question 1" -ForegroundColor Green
    foreach ($answer in $response.Data) {
        $correctText = if ($answer.is_correct) { " (CORRECT)" } else { "" }
        Write-Host "   - $($answer.answer_text)$correctText" -ForegroundColor Gray
    }
    Write-Host ""
} catch {
    Write-Host "❌ Failed to retrieve question answers: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 8: Get Complete Quiz
Write-Host "8. Testing Get Complete Quiz (ID: 1)..." -ForegroundColor Cyan
try {
    $response = Invoke-RestMethod -Uri "$baseUrl/quiz_complete/1"
    $questionCount = $response.Data.questions.Count
    Write-Host "✅ Retrieved complete quiz with $questionCount questions" -ForegroundColor Green
    Write-Host "   Quiz: $($response.Data.title)" -ForegroundColor Gray
    foreach ($question in $response.Data.questions) {
        $answerCount = $question.answers.Count
        Write-Host "   - $($question.question_text) ($answerCount answers)" -ForegroundColor Gray
    }
    Write-Host ""
} catch {
    Write-Host "❌ Failed to retrieve complete quiz: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
}

# Test 9: Test Different Categories
Write-Host "9. Testing All Categories..." -ForegroundColor Cyan
$categories = @("General", "Science", "History", "Programming", "Geography")
foreach ($category in $categories) {
    try {
        $response = Invoke-RestMethod -Uri "$baseUrl/quizzes?category=$category"
        $count = $response.Data.Count
        Write-Host "   $category`: $count quiz(s)" -ForegroundColor Gray
    } catch {
        Write-Host "   $category`: Error - $($_.Exception.Message)" -ForegroundColor Red
    }
}
Write-Host ""

# Test 10: Create, Update, Delete Test (Optional)
Write-Host "10. Testing CRUD Operations..." -ForegroundColor Cyan
Write-Host "   Note: This will create and delete test data" -ForegroundColor Yellow

$testQuiz = @{
    title = "Test Quiz - PowerShell"
    description = "A test quiz created by PowerShell script"
    category = "Testing"
    tags = "test,powershell,api"
} | ConvertTo-Json

try {
    # Create Quiz
    $createResponse = Invoke-RestMethod -Uri "$baseUrl/quizzes" -Method POST -Body $testQuiz -ContentType "application/json"
    $newQuizId = $createResponse.Data.id
    Write-Host "✅ Created test quiz with ID: $newQuizId" -ForegroundColor Green
    
    # Update Quiz
    $updateQuiz = @{
        title = "Updated Test Quiz - PowerShell"
        description = "An updated test quiz"
        category = "Testing"
        tags = "test,powershell,api,updated"
    } | ConvertTo-Json
    
    $updateResponse = Invoke-RestMethod -Uri "$baseUrl/quizzes/$newQuizId" -Method PUT -Body $updateQuiz -ContentType "application/json"
    Write-Host "✅ Updated test quiz successfully" -ForegroundColor Green
    
    # Delete Quiz
    $deleteResponse = Invoke-RestMethod -Uri "$baseUrl/quizzes/$newQuizId" -Method DELETE
    Write-Host "✅ Deleted test quiz successfully" -ForegroundColor Green
    
} catch {
    Write-Host "❌ CRUD operations failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== Testing Complete ===" -ForegroundColor Green
Write-Host "Quiz API is ready for use!" -ForegroundColor Yellow
Write-Host ""
Write-Host "Access Points:" -ForegroundColor Cyan
Write-Host "- API Base URL: http://localhost:8080" -ForegroundColor Gray
Write-Host "- API Documentation: http://localhost:8080" -ForegroundColor Gray
Write-Host "- phpMyAdmin: http://localhost:8081" -ForegroundColor Gray
Write-Host "  Username: user" -ForegroundColor Gray
Write-Host "  Password: userpassword" -ForegroundColor Gray
