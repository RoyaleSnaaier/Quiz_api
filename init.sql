-- Quiz Database Sample Data
-- This file uses the original database structure from datbase.sql
-- It only adds comprehensive sample data for testing

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

-- ===========================================
-- CREATE TABLES USING ORIGINAL STRUCTURE
-- ===========================================

-- Dumping structure for table quiz_db.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tel` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table quiz_db.quizzes
CREATE TABLE IF NOT EXISTS `quizzes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'Custom quiz',
  `description` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table quiz_db.questions
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table quiz_db.question_type
CREATE TABLE IF NOT EXISTS `question_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `type` enum('MC','YN') DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_question_type_questions` (`question_id`),
  CONSTRAINT `FK_question_type_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping structure for table quiz_db.question_options
CREATE TABLE IF NOT EXISTS `question_options` (
  `id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `is_correct` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_options_questions` (`question_id`),
  CONSTRAINT `FK_options_questions` FOREIGN KEY (`question_id`) REFERENCES `questions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ===========================================
-- INSERT SAMPLE DATA
-- ===========================================

-- Insert sample users for CMS authentication
INSERT INTO `users` (`username`, `password`, `email`, `tel`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@quizapi.com', '123-456-7890'),
('editor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'editor@quizapi.com', '123-456-7891'),
('john_doe', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'john@example.com', '555-0123'),
('jane_smith', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jane@example.com', '555-0124'),
('mike_wilson', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mike@example.com', '555-0125');

-- Insert sample quizzes
INSERT INTO `quizzes` (`name`, `description`) VALUES
('World Geography', 'Test your knowledge of world capitals, countries, and landmarks'),
('Basic Science', 'Fundamental concepts in physics, chemistry, and biology'),
('History Trivia', 'Important historical events and figures'),
('Programming Basics', 'Basic programming concepts and languages'),
('Sports Knowledge', 'Famous athletes, teams, and sporting events'),
('General Knowledge', 'Mixed topics covering various subjects');

-- Insert questions for World Geography Quiz (Quiz ID: 1)
INSERT INTO `questions` (`quiz_id`, `type_id`, `question`, `time`) VALUES
(1, 1, 'What is the capital of Australia?', '00:00:30'),
(1, 1, 'Which country has the most time zones?', '00:00:45'),
(1, 1, 'What is the longest river in the world?', '00:00:30'),
(1, 2, 'The Great Wall of China is visible from space.', '00:00:20'),
(1, 1, 'Which continent has the most countries?', '00:00:30');

-- Insert questions for Basic Science Quiz (Quiz ID: 2)
INSERT INTO `questions` (`quiz_id`, `type_id`, `question`, `time`) VALUES
(2, 1, 'What is the chemical symbol for water?', '00:00:20'),
(2, 1, 'How many planets are in our solar system?', '00:00:30'),
(2, 1, 'What is the largest organ in the human body?', '00:00:25'),
(2, 2, 'Diamonds are made of carbon.', '00:00:20'),
(2, 1, 'What gas do plants absorb during photosynthesis?', '00:00:30');

-- Insert questions for Programming Basics Quiz (Quiz ID: 4)
INSERT INTO `questions` (`quiz_id`, `type_id`, `question`, `time`) VALUES
(4, 1, 'Which of these is NOT a programming language?', '00:00:25'),
(4, 1, 'What does HTML stand for?', '00:00:30'),
(4, 1, 'Which company developed the Java programming language?', '00:00:30'),
(4, 2, 'Python is a compiled programming language.', '00:00:25'),
(4, 1, 'What does API stand for?', '00:00:35');

-- Insert questions for Sports Knowledge Quiz (Quiz ID: 5)
INSERT INTO `questions` (`quiz_id`, `type_id`, `question`, `time`) VALUES
(5, 1, 'How many players are on a basketball team on court?', '00:00:20'),
(5, 1, 'Which country won the FIFA World Cup 2018?', '00:00:30'),
(5, 2, 'The Olympics are held every 4 years.', '00:00:15'),
(5, 1, 'In which sport would you perform a slam dunk?', '00:00:25'),
(5, 1, 'How many rings are on the Olympic flag?', '00:00:20');

-- Insert questions for General Knowledge Quiz (Quiz ID: 6)
INSERT INTO `questions` (`quiz_id`, `type_id`, `question`, `time`) VALUES
(6, 1, 'Who wrote "Romeo and Juliet"?', '00:00:25'),
(6, 1, 'What is the currency of Japan?', '00:00:20'),
(6, 2, 'The human body has 206 bones.', '00:00:25'),
(6, 1, 'Which planet is known as the "Red Planet"?', '00:00:20'),
(6, 1, 'What is the largest mammal in the world?', '00:00:25');

-- Insert question types for each question
-- Questions 1-5 (World Geography)
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(1, 'MC'), (2, 'MC'), (3, 'MC'), (4, 'YN'), (5, 'MC');

-- Questions 6-10 (Basic Science)
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(6, 'MC'), (7, 'MC'), (8, 'MC'), (9, 'YN'), (10, 'MC');

-- Questions 11-15 (Programming Basics)
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(11, 'MC'), (12, 'MC'), (13, 'MC'), (14, 'YN'), (15, 'MC');

-- Questions 16-20 (Sports Knowledge)
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(16, 'MC'), (17, 'MC'), (18, 'YN'), (19, 'MC'), (20, 'MC');

-- Questions 21-25 (General Knowledge)
INSERT INTO `question_type` (`question_id`, `type`) VALUES
(21, 'MC'), (22, 'MC'), (23, 'YN'), (24, 'MC'), (25, 'MC');

-- ===========================================
-- INSERT QUESTION OPTIONS
-- ===========================================

-- World Geography Quiz Options (Questions 1-5)

-- Question 1: What is the capital of Australia?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(1, 'Sydney', 0),
(1, 'Melbourne', 0),
(1, 'Canberra', 1),
(1, 'Perth', 0);

-- Question 2: Which country has the most time zones?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(2, 'Russia', 1),
(2, 'United States', 0),
(2, 'China', 0),
(2, 'Canada', 0);

-- Question 3: What is the longest river in the world?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(3, 'Amazon River', 1),
(3, 'Nile River', 0),
(3, 'Mississippi River', 0),
(3, 'Yangtze River', 0);

-- Question 4: The Great Wall of China is visible from space (True/False)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(4, 'True', 0),
(4, 'False', 1);

-- Question 5: Which continent has the most countries?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(5, 'Africa', 1),
(5, 'Asia', 0),
(5, 'Europe', 0),
(5, 'South America', 0);

-- Basic Science Quiz Options (Questions 6-10)

-- Question 6: What is the chemical symbol for water?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(6, 'H2O', 1),
(6, 'CO2', 0),
(6, 'O2', 0),
(6, 'NaCl', 0);

-- Question 7: How many planets are in our solar system?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(7, '7', 0),
(7, '8', 1),
(7, '9', 0),
(7, '10', 0);

-- Question 8: What is the largest organ in the human body?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(8, 'Heart', 0),
(8, 'Brain', 0),
(8, 'Liver', 0),
(8, 'Skin', 1);

-- Question 9: Diamonds are made of carbon (True/False)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(9, 'True', 1),
(9, 'False', 0);

-- Question 10: What gas do plants absorb during photosynthesis?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(10, 'Oxygen', 0),
(10, 'Carbon Dioxide', 1),
(10, 'Nitrogen', 0),
(10, 'Hydrogen', 0);

-- Programming Basics Quiz Options (Questions 11-15)

-- Question 11: Which of these is NOT a programming language?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(11, 'Python', 0),
(11, 'Java', 0),
(11, 'HTML', 1),
(11, 'C++', 0);

-- Question 12: What does HTML stand for?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(12, 'HyperText Markup Language', 1),
(12, 'High Tech Modern Language', 0),
(12, 'Home Tool Markup Language', 0),
(12, 'Hyperlink and Text Markup Language', 0);

-- Question 13: Which company developed the Java programming language?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(13, 'Microsoft', 0),
(13, 'Apple', 0),
(13, 'Sun Microsystems', 1),
(13, 'IBM', 0);

-- Question 14: Python is a compiled programming language (True/False)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(14, 'True', 0),
(14, 'False', 1);

-- Question 15: What does API stand for?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(15, 'Application Programming Interface', 1),
(15, 'Advanced Programming Integration', 0),
(15, 'Automated Program Interaction', 0),
(15, 'Application Process Integration', 0);

-- Sports Knowledge Quiz Options (Questions 16-20)

-- Question 16: How many players are on a basketball team on court?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(16, '4', 0),
(16, '5', 1),
(16, '6', 0),
(16, '7', 0);

-- Question 17: Which country won the FIFA World Cup 2018?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(17, 'Brazil', 0),
(17, 'Germany', 0),
(17, 'France', 1),
(17, 'Argentina', 0);

-- Question 18: The Olympics are held every 4 years (True/False)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(18, 'True', 1),
(18, 'False', 0);

-- Question 19: In which sport would you perform a slam dunk?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(19, 'Football', 0),
(19, 'Basketball', 1),
(19, 'Tennis', 0),
(19, 'Baseball', 0);

-- Question 20: How many rings are on the Olympic flag?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(20, '4', 0),
(20, '5', 1),
(20, '6', 0),
(20, '7', 0);

-- General Knowledge Quiz Options (Questions 21-25)

-- Question 21: Who wrote "Romeo and Juliet"?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(21, 'Charles Dickens', 0),
(21, 'William Shakespeare', 1),
(21, 'Mark Twain', 0),
(21, 'Jane Austen', 0);

-- Question 22: What is the currency of Japan?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(22, 'Yuan', 0),
(22, 'Won', 0),
(22, 'Yen', 1),
(22, 'Rupee', 0);

-- Question 23: The human body has 206 bones (True/False)
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(23, 'True', 1),
(23, 'False', 0);

-- Question 24: Which planet is known as the "Red Planet"?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(24, 'Venus', 0),
(24, 'Mars', 1),
(24, 'Jupiter', 0),
(24, 'Saturn', 0);

-- Question 25: What is the largest mammal in the world?
INSERT INTO `question_options` (`question_id`, `option_text`, `is_correct`) VALUES
(25, 'African Elephant', 0),
(25, 'Blue Whale', 1),
(25, 'Giraffe', 0),
(25, 'Hippopotamus', 0);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;