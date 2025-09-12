-- Quiz Database with Dummy Data
-- This file will be automatically loaded when the MySQL container starts

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `quiz_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `quiz_db`;

-- Table structure for table `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tel` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `quizzes`
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Custom quiz',
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `questions`
CREATE TABLE IF NOT EXISTS `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `type_id` smallint DEFAULT NULL,
  `question` varchar(255) NOT NULL,
  `time` time DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `FK_questions_quizzes` (`quiz_id`),
  CONSTRAINT `FK_questions_quizzes` FOREIGN KEY (`quiz_id`) REFERENCES `quizzes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table structure for table `question_type`
CREATE TABLE IF NOT EXISTS `question_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `type` enum('MC','YN') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_question_type_questions` (`question_id`),
  CONSTRAINT `FK_question_type_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Table for multiple choice options
CREATE TABLE IF NOT EXISTS `question_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` boolean DEFAULT FALSE,
  PRIMARY KEY (`id`),
  KEY `FK_options_questions` (`question_id`),
  CONSTRAINT `FK_options_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Insert dummy users
INSERT INTO `users` (`username`, `password`, `email`, `tel`) VALUES
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', '+1234567890'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@example.com', '+1234567891'),
('mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike@example.com', '+1234567892'),
('sarah_davis', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarah@example.com', '+1234567893'),
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@quizapi.com', '+1234567894');

-- Insert dummy quizzes
INSERT INTO `quizzes` (`name`, `description`) VALUES
('General Knowledge Quiz', 'Test your general knowledge with this fun quiz covering various topics.'),
('Science Quiz', 'Challenge yourself with questions about physics, chemistry, and biology.'),
('History Quiz', 'Explore important historical events and figures from around the world.'),
('Programming Quiz', 'Test your programming knowledge with questions about various languages and concepts.'),
('Geography Quiz', 'How well do you know world geography? Countries, capitals, and landmarks!');

-- Insert dummy questions for General Knowledge Quiz (ID: 1)
INSERT INTO `questions` (`quiz_id`, `question`, `time`) VALUES
(1, 'What is the capital of France?', '00:00:30'),
(1, 'Which planet is known as the Red Planet?', '00:00:30'),
(1, 'Who painted the Mona Lisa?', '00:00:45'),
(1, 'What is the largest ocean on Earth?', '00:00:30'),
(1, 'In which year did World War II end?', '00:00:45');

-- Insert dummy questions for Science Quiz (ID: 2)
INSERT INTO `questions` (`quiz_id`, `question`, `time`) VALUES
(2, 'What is the chemical symbol for gold?', '00:00:30'),
(2, 'How many bones are in an adult human body?', '00:00:45'),
(2, 'What is the speed of light in vacuum?', '00:01:00'),
(2, 'Which gas makes up most of Earth\'s atmosphere?', '00:00:30'),
(2, 'What is the smallest unit of matter?', '00:00:45');

-- Insert dummy questions for Programming Quiz (ID: 4)
INSERT INTO `questions` (`quiz_id`, `question`, `time`) VALUES
(4, 'Which programming language is known as the backbone of web development?', '00:00:30'),
(4, 'What does API stand for?', '00:00:45'),
(4, 'Which of the following is a relational database?', '00:00:30'),
(4, 'What is the correct way to declare a variable in PHP?', '00:00:45');

-- Insert question types
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(1, 'MC'), (2, 'MC'), (3, 'MC'), (4, 'MC'), (5, 'MC'),
(6, 'MC'), (7, 'MC'), (8, 'MC'), (9, 'MC'), (10, 'MC'),
(11, 'MC'), (12, 'MC'), (13, 'MC'), (14, 'MC');

-- Insert multiple choice options for General Knowledge Quiz
-- Question 1: Capital of France
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(1, 'London', FALSE),
(1, 'Berlin', FALSE),
(1, 'Paris', TRUE),
(1, 'Madrid', FALSE);

-- Question 2: Red Planet
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(2, 'Venus', FALSE),
(2, 'Mars', TRUE),
(2, 'Jupiter', FALSE),
(2, 'Saturn', FALSE);

-- Question 3: Mona Lisa
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(3, 'Vincent van Gogh', FALSE),
(3, 'Pablo Picasso', FALSE),
(3, 'Leonardo da Vinci', TRUE),
(3, 'Michelangelo', FALSE);

-- Question 4: Largest Ocean
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(4, 'Atlantic Ocean', FALSE),
(4, 'Indian Ocean', FALSE),
(4, 'Arctic Ocean', FALSE),
(4, 'Pacific Ocean', TRUE);

-- Question 5: WWII End
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(5, '1944', FALSE),
(5, '1945', TRUE),
(5, '1946', FALSE),
(5, '1947', FALSE);

-- Insert multiple choice options for Science Quiz
-- Question 6: Gold symbol
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(6, 'Go', FALSE),
(6, 'Au', TRUE),
(6, 'Ag', FALSE),
(6, 'Gd', FALSE);

-- Question 7: Human bones
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(7, '196', FALSE),
(7, '206', TRUE),
(7, '216', FALSE),
(7, '186', FALSE);

-- Question 8: Speed of light
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(8, '299,792,458 m/s', TRUE),
(8, '300,000,000 m/s', FALSE),
(8, '299,792,458 km/s', FALSE),
(8, '186,000 miles/s', FALSE);

-- Question 9: Earth's atmosphere
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(9, 'Oxygen', FALSE),
(9, 'Carbon Dioxide', FALSE),
(9, 'Nitrogen', TRUE),
(9, 'Argon', FALSE);

-- Question 10: Smallest unit of matter
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(10, 'Molecule', FALSE),
(10, 'Atom', TRUE),
(10, 'Electron', FALSE),
(10, 'Proton', FALSE);

-- Insert multiple choice options for Programming Quiz
-- Question 11: Web development backbone
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(11, 'Python', FALSE),
(11, 'JavaScript', TRUE),
(11, 'Java', FALSE),
(11, 'C++', FALSE);

-- Question 12: API meaning
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(12, 'Application Programming Interface', TRUE),
(12, 'Advanced Programming Integration', FALSE),
(12, 'Automated Program Interaction', FALSE),
(12, 'Application Process Integration', FALSE);

-- Question 13: Relational database
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(13, 'MongoDB', FALSE),
(13, 'Redis', FALSE),
(13, 'MySQL', TRUE),
(13, 'Elasticsearch', FALSE);

-- Question 14: PHP variable declaration
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(14, 'var $name', FALSE),
(14, '$name', TRUE),
(14, 'let $name', FALSE),
(14, 'string $name', FALSE);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;