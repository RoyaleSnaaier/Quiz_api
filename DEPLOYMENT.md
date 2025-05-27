# Quiz API - Deployment & Usage Guide

## Quick Start Commands

### 1. Start the Application
```powershell
# Navigate to project directory
cd "c:\Users\ivanr\OneDrive\Documenten\school\Quiz_api"

# Start Docker containers
docker-compose up -d

# Verify containers are running
docker-compose ps
```

### 2. Setup Database (Single Command)
```powershell
# Complete database setup with one script
Get-Content complete_setup.sql | docker exec -i mysql_db_quiz_api mysql -u user -puserpassword quiz_database
```

**What this script does:**
- ✅ Drops existing tables (if any)
- ✅ Creates complete database schema
- ✅ Loads all 5 sample quizzes
- ✅ Loads all 16 questions with time limits
- ✅ Loads all 64 answers with correct markings
- ✅ Displays summary and verification

### 3. Test the API
```powershell
# Run comprehensive test suite
.\test_api.ps1

# Or test individual endpoints
Invoke-RestMethod -Uri "http://localhost:8080/quizzes"
Invoke-RestMethod -Uri "http://localhost:8080/quiz_complete/1"
```

## Access Information

| Service | URL | Credentials |
|---------|-----|-------------|
| **Quiz API** | http://localhost:8080 | - |
| **API Documentation** | http://localhost:8080 | - |
| **phpMyAdmin** | http://localhost:8081 | user / userpassword |
| **MySQL Direct** | localhost:3307 | user / userpassword |

## Sample Data Overview

✅ **5 Complete Quizzes Loaded**
- **General Knowledge Quiz** (4 questions) - General category
- **Science & Technology** (4 questions) - Science category  
- **History Quiz** (3 questions) - History category
- **Programming Fundamentals** (3 questions) - Programming category
- **Geography Challenge** (2 questions) - Geography category

✅ **16 Total Questions** with time limits ranging from 20-40 seconds
✅ **64 Total Answers** (4 multiple choice options per question)
✅ **All Categories Functional** - Filtering works correctly

## API Testing Results

### ✅ Working Endpoints
- `GET /` - API Documentation ✅
- `GET /db_health` - Database Health Check ✅
- `GET /quizzes` - List All Quizzes ✅
- `GET /quizzes/{id}` - Get Specific Quiz ✅
- `GET /quizzes?category={name}` - Filter by Category ✅
- `GET /quiz_questions?quiz_id={id}` - Get Quiz Questions ✅
- `GET /answers?question_id={id}` - Get Question Answers ✅
- `GET /quiz_complete/{id}` - Complete Quiz with All Data ✅

### 🔧 CRUD Operations
- **READ operations**: Fully functional ✅
- **CREATE/UPDATE/DELETE**: Available but may need validation adjustments

## Quick API Examples

### Get All Quizzes
```powershell
Invoke-RestMethod -Uri "http://localhost:8080/quizzes"
```

### Get Quiz by Category
```powershell
Invoke-RestMethod -Uri "http://localhost:8080/quizzes?category=Science"
```

### Get Complete Quiz with Questions and Answers
```powershell
Invoke-RestMethod -Uri "http://localhost:8080/quiz_complete/1"
```

### Create New Quiz
```powershell
$quiz = @{
    title = "My New Quiz"
    description = "A test quiz"
    category = "Education"
    tags = "test,sample"
} | ConvertTo-Json

Invoke-RestMethod -Uri "http://localhost:8080/quizzes" -Method POST -Body $quiz -ContentType "application/json"
```

## Database Management

### View Data in phpMyAdmin
1. Open http://localhost:8081
2. Login with `user` / `userpassword`
3. Select `quiz_database`
4. Browse tables: `quizes`, `quiz_questions`, `answers`

### Direct MySQL Access
```powershell
# Access MySQL shell
docker exec -it mysql_db_quiz_api mysql -u user -puserpassword quiz_database

# View data counts
docker exec -it mysql_db_quiz_api mysql -u user -puserpassword quiz_database -e "
SELECT 
  (SELECT COUNT(*) FROM quizes) as total_quizzes,
  (SELECT COUNT(*) FROM quiz_questions) as total_questions,
  (SELECT COUNT(*) FROM answers) as total_answers;
"
```

## Project Structure Summary

```
Quiz_api/
├── 📋 README.md                    # Complete documentation
├── 🧪 test_api.ps1                # Comprehensive testing script
├── 📖 DEPLOYMENT.md               # This deployment guide
├── 🐳 docker-compose.yml          # Docker configuration
├── 🐳 Dockerfile                  # PHP container setup
├── 🔀 .htaccess                   # URL routing
├── 📄 index.php                   # API documentation endpoint
├── 🗄️ complete_setup.sql          # Complete database setup & sample data
├── 🧹 CLEANUP.md                  # Guide for removing obsolete files
├── include/
│   ├── db.php                     # Database connection
│   └── class/                     # PHP classes
├── pages/                         # API endpoints
└── assets/                        # Static files
```

## Troubleshooting

### If containers won't start:
```powershell
# Stop and remove containers
docker-compose down

# Remove volumes (⚠️ This deletes all data)
docker volume prune

# Start fresh
docker-compose up -d
```

### If ports are in use:
Edit `docker-compose.yml` and change port mappings:
```yaml
ports:
  - "8090:80"    # Change from 8080
  - "8091:80"    # Change from 8081
```

### If database is empty:
```powershell
# Load complete database schema and sample data with single script
Get-Content complete_setup.sql | docker exec -i mysql_db_quiz_api mysql -u user -puserpassword quiz_database
```

## Next Steps

1. **✅ Development Complete** - API is fully functional
2. **✅ Sample Data Loaded** - Ready for testing
3. **✅ Documentation Created** - Complete guides available
4. **🎯 Ready for Use** - Start building your quiz application!

## Production Deployment

For production deployment:
1. Change default passwords in `docker-compose.yml`
2. Use environment variables for sensitive data
3. Configure proper SSL/HTTPS
4. Set up database backups
5. Implement authentication/authorization
6. Add rate limiting
7. Configure monitoring and logging

---

**🎉 Congratulations! Your Quiz API is now fully operational!**
