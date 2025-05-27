# Quiz API

A complete RESTful Quiz Management API built with PHP, MySQL, and Docker. This API supports full CRUD operations for quizzes, questions, and answers with advanced features like time limits, categories, and image support.

## Features

- 🎯 **Complete CRUD Operations** for quizzes, questions, and answers
- ⏱️ **Time Limits** for individual questions
- 🏷️ **Categories and Tags** for quiz organization
- 🖼️ **Image Support** for quizzes, questions, and answers
- 🔗 **Clean URL Routing** with RESTful endpoints
- 🐳 **Docker Support** for easy deployment
- 💾 **MySQL Database** with proper foreign key relationships
- 📊 **phpMyAdmin** for database management
- 🧪 **Sample Data** included for testing

## Quick Start

### Prerequisites
- Docker and Docker Compose
- Git

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd Quiz_api
```

2. **Start the application**
```bash
docker-compose up -d
```

3. **Load database schema and sample data**
```bash
# Load complete database setup with sample data
docker exec -i mysql_db_quiz_api mysql -u user -puserpassword quiz_database < complete_setup.sql
```

### Access Points

- **API Base URL**: http://localhost:8080
- **API Documentation**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
  - Username: `user`
  - Password: `userpassword`
  - Database: `quiz_database`

## API Endpoints

### Quizzes
- `GET /quizzes` - Get all quizzes (supports `?category=` filter)
- `GET /quizzes/{id}` - Get specific quiz
- `POST /quizzes` - Create new quiz
- `PUT /quizzes/{id}` - Update quiz
- `DELETE /quizzes/{id}` - Delete quiz

### Quiz Questions
- `GET /quiz_questions` - Get all questions (supports `?quiz_id=` filter)
- `GET /quiz_questions/{id}` - Get specific question
- `POST /quiz_questions` - Create new question
- `PUT /quiz_questions/{id}` - Update question
- `DELETE /quiz_questions/{id}` - Delete question

### Answers
- `GET /answers` - Get all answers (supports `?question_id=` filter)
- `GET /answers/{id}` - Get specific answer
- `POST /answers` - Create new answer
- `PUT /answers/{id}` - Update answer
- `DELETE /answers/{id}` - Delete answer

### Special Endpoints
- `GET /quiz_complete/{id}` - Get complete quiz with all questions and answers
- `GET /db_health` - Check database connection status

## Database Schema

### Tables

#### `quizes`
- `id` - Primary key
- `title` - Quiz title (required)
- `description` - Quiz description (required)
- `category` - Quiz category (optional)
- `tags` - Comma-separated tags (optional)
- `image_url` - Quiz image URL (optional)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `quiz_questions`
- `id` - Primary key
- `quiz_id` - Foreign key to quizes.id
- `question_text` - The question text (required)
- `question_type` - Type: multiple_choice, true_false, text (default: multiple_choice)
- `time_limit` - Time limit in seconds (optional)
- `image_url` - Question image URL (optional)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

#### `answers`
- `id` - Primary key
- `quiz_id` - Foreign key to quizes.id
- `question_id` - Foreign key to quiz_questions.id
- `answer_text` - The answer text (required)
- `is_correct` - Whether this is the correct answer (boolean)
- `image_url` - Answer image URL (optional)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

## Sample Data

The API comes with comprehensive sample data including:

- **5 Quizzes** covering different categories:
  - General Knowledge Quiz (4 questions)
  - Science & Technology (4 questions)
  - History Quiz (3 questions)
  - Programming Fundamentals (3 questions)
  - Geography Challenge (2 questions)

- **16 Questions** total with varied time limits
- **64 Answers** (4 per question) with one correct answer each
- **Categories**: General, Science, History, Programming, Geography

## API Usage Examples

### Create a Quiz
```bash
curl -X POST http://localhost:8080/quizzes \
  -H "Content-Type: application/json" \
  -d '{
    "title": "My Quiz",
    "description": "A sample quiz",
    "category": "Education",
    "tags": "test,sample"
  }'
```

### Create a Question
```bash
curl -X POST http://localhost:8080/quiz_questions \
  -H "Content-Type: application/json" \
  -d '{
    "quiz_id": 1,
    "question_text": "What is 2+2?",
    "question_type": "multiple_choice",
    "time_limit": 30
  }'
```

### Create an Answer
```bash
curl -X POST http://localhost:8080/answers \
  -H "Content-Type: application/json" \
  -d '{
    "quiz_id": 1,
    "question_id": 1,
    "answer_text": "4",
    "is_correct": true
  }'
```

### Get Complete Quiz
```bash
curl http://localhost:8080/quiz_complete/1
```

## Testing

### Quick API Tests

Test the API endpoints with these curl commands:

```bash
# Test all quizzes
curl http://localhost:8080/quizzes

# Test specific quiz
curl http://localhost:8080/quizzes/1

# Test complete quiz
curl http://localhost:8080/quiz_complete/1

# Test category filter
curl "http://localhost:8080/quizzes?category=Science"

# Test database health
curl http://localhost:8080/db_health
```

### PowerShell Testing (Windows)

```powershell
# Test all quizzes
Invoke-RestMethod -Uri "http://localhost:8080/quizzes"

# Test specific quiz
Invoke-RestMethod -Uri "http://localhost:8080/quizzes/1"

# Test complete quiz
Invoke-RestMethod -Uri "http://localhost:8080/quiz_complete/1"
```

## File Structure

```
Quiz_api/
├── docker-compose.yml      # Docker configuration
├── Dockerfile             # PHP container configuration
├── .htaccess              # URL routing rules
├── index.php              # API documentation endpoint
├── complete_setup.sql     # Complete database setup & sample data
├── CLEANUP.md             # Guide for removing obsolete files
├── include/
│   ├── db.php            # Database connection
│   └── class/
│       ├── quiz.php      # Quiz class
│       ├── quiz_question.php # Question class
│       ├── answer.php    # Answer class
│       └── response.php  # API response class
└── pages/
    ├── quizzes.php       # Quiz CRUD operations
    ├── quiz_questions.php # Question CRUD operations
    ├── answers.php       # Answer CRUD operations
    ├── quiz_complete.php # Complete quiz endpoint
    └── db_health.php     # Database health check
```

## Development

### Adding New Features

1. **Add new endpoints**: Create new PHP files in the `pages/` directory
2. **Update routing**: Modify `.htaccess` to include new routes
3. **Database changes**: Update `complete_setup.sql` with new schema and data
4. **Classes**: Add new classes in `include/class/` as needed

### Database Management

- Access phpMyAdmin at http://localhost:8081
- Direct MySQL access: `docker exec -it mysql_db_quiz_api mysql -u user -puserpassword quiz_database`
- Backup database: `docker exec mysql_db_quiz_api mysqldump -u user -puserpassword quiz_database > backup.sql`

## Troubleshooting

### Common Issues

1. **Port conflicts**: If port 8080 or 8081 are in use, modify `docker-compose.yml`
2. **Database connection**: Check container status with `docker-compose ps`
3. **Logs**: View logs with `docker-compose logs -f`

### Reset Database

```bash
# Stop containers
docker-compose down

# Remove volumes (this will delete all data)
docker volume rm quiz_api_db_data

# Start fresh
docker-compose up -d

# Reload database with complete setup
docker exec -i mysql_db_quiz_api mysql -u user -puserpassword quiz_database < complete_setup.sql
```

## License

This project is open source and available under the [MIT License](LICENSE).

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For issues and questions, please create an issue in the repository or contact the development team.
