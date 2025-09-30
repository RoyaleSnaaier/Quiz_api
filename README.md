# Quiz API

A backend API for managing quizzes. Implements clean architecture and standard CRUD operations. Supports Docker deployment.

---

## Table of Contents

- [Description](#description)  
- [Tech Stack](#tech-stack)  
- [Project Structure](#project-structure)  
- [Endpoints](#endpoints)  
- [Setup & Running](#setup--running)  

---

## Description

This API allows clients to create, read, update, and delete quizzes, questions, and related resources. It is designed with security, modularity, and scalability in mind.

---

## Tech Stack

- PHP  
- Docker / Docker Compose  
- Clean Architecture / layered structure  
- Configuration files, environment variables  

---

## Project Structure

├── api/ # Endpoint controllers, routes
├── config/ # Configuration, settings, environment handling
├── docker/ # Docker files and related scripts
├── .env # Environment variables (not committed)
├── Dockerfile
├── docker-compose.yml
├── index.php # Entry point
├── database.sql / init.sql # Schema / seed data
├── .htaccess # Apache / web server config
└── other supporting files


- `api/` — contains route definitions, controllers, and handlers  
- `config/` — configuration logic (database, auth, etc.)  
- `docker/` — Docker setup and related resources  
- `index.php` — main entry point for HTTP requests  
- SQL files — for initializing the database  

---

## Endpoints

Below is a sample of the endpoints this API might expose. Adjust names and paths to your implementation.

| Method | Path                    | Description                             |
|--------|--------------------------|-----------------------------------------|
| GET    | `/quizzes`               | List all quizzes                        |
| GET    | `/quizzes/{id}`          | Get one quiz by ID                      |
| POST   | `/quizzes`               | Create a new quiz                       |
| PUT    | `/quizzes/{id}`          | Update an existing quiz                 |
| DELETE | `/quizzes/{id}`          | Delete a quiz                           |
| GET    | `/quizzes/{id}/questions`| List questions for a quiz               |
| POST   | `/questions`              | Create a question                       |
| PUT    | `/questions/{id}`         | Update a question                       |
| DELETE | `/questions/{id}`         | Delete a question                       |
| POST   | `/auth/login`             | Authenticate user / issue token         |
| GET    | `/users/{id}`             | Fetch user details (if applicable)      |

> Note: Exact paths, parameter names, authentication methods, and response formats should match your implementation.

---

## Setup & Running

1. Clone the repository  
2. Create a `.env` file (copy from `.env.example` or config template) with your credentials and settings  
3. Ensure Docker and Docker Compose are installed  
4. Run:
   ```bash
   docker-compose up --build
5. The API should become available at http://localhost:YOUR_PORT (configured in docker-compose.yml)
6. Run database initialization or migrations if needed
