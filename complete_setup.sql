-- ==============================================================================
-- COMPLETE QUIZ API DATABASE SETUP SCRIPT
-- ==============================================================================
-- This script creates the complete database schema and loads sample data
-- Run this script to set up everything from scratch
-- ==============================================================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS answers;
DROP TABLE IF EXISTS quiz_questions;
DROP TABLE IF EXISTS quizes;

-- ==============================================================================
-- SCHEMA CREATION
-- ==============================================================================

-- Create quizes table
CREATE TABLE quizes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) DEFAULT NULL,
    tags TEXT DEFAULT NULL COMMENT 'Comma-separated tags',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create quiz_questions table
CREATE TABLE quiz_questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_text TEXT NOT NULL,
    question_type VARCHAR(50) DEFAULT 'multiple_choice',
    time_limit INT DEFAULT NULL COMMENT 'Time limit in seconds for this question, NULL for no limit',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizes(id) ON DELETE CASCADE
);

-- Create answers table
CREATE TABLE answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT NOT NULL,
    question_id INT NOT NULL,
    answer_text TEXT NOT NULL,
    is_correct BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (quiz_id) REFERENCES quizes(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(id) ON DELETE CASCADE
);

-- ==============================================================================
-- SAMPLE DATA INSERTION
-- ==============================================================================

-- Insert Sample Quizzes
INSERT INTO quizes (title, description, category, tags, image_url, created_at) VALUES
('General Knowledge Quiz', 'Test your general knowledge with this fun quiz covering various topics', 'General', 'knowledge,trivia,general,fun', 'https://images.unsplash.com/photo-1606092195730-5d7b9af1efc5?w=400', NOW()),
('Science & Technology', 'Challenge yourself with questions about science, technology, and innovation', 'Science', 'science,technology,innovation,STEM', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=400', NOW()),
('History Quiz', 'Journey through time with questions about world history and important events', 'History', 'history,world,events,timeline', 'https://images.unsplash.com/photo-1461360370896-922624d12aa1?w=400', NOW()),
('Programming Fundamentals', 'Test your programming knowledge with questions about coding concepts', 'Programming', 'programming,coding,development,computer science', 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?w=400', NOW()),
('Geography Challenge', 'Explore the world through geography questions about countries, capitals, and landmarks', 'Geography', 'geography,world,countries,capitals,travel', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400', NOW());

-- Insert Sample Questions for Quiz 1 (General Knowledge)
INSERT INTO quiz_questions (quiz_id, question_text, question_type, time_limit, image_url) VALUES
(1, 'What is the capital of France?', 'multiple_choice', 30, NULL),
(1, 'Which planet is known as the Red Planet?', 'multiple_choice', 25, 'https://images.unsplash.com/photo-1614732414444-096e5f1122d5?w=400'),
(1, 'What is the largest mammal in the world?', 'multiple_choice', 20, NULL),
(1, 'In which year did World War II end?', 'multiple_choice', 35, NULL);

-- Insert Sample Questions for Quiz 2 (Science & Technology)
INSERT INTO quiz_questions (quiz_id, question_text, question_type, time_limit, image_url) VALUES
(2, 'What does DNA stand for?', 'multiple_choice', 30, NULL),
(2, 'Which element has the chemical symbol "O"?', 'multiple_choice', 20, NULL),
(2, 'What is the speed of light in vacuum?', 'multiple_choice', 40, NULL),
(2, 'Who invented the World Wide Web?', 'multiple_choice', 35, NULL);

-- Insert Sample Questions for Quiz 3 (History)
INSERT INTO quiz_questions (quiz_id, question_text, question_type, time_limit, image_url) VALUES
(3, 'Who was the first President of the United States?', 'multiple_choice', 25, NULL),
(3, 'In which year did the Berlin Wall fall?', 'multiple_choice', 30, NULL),
(3, 'Which ancient wonder of the world was located in Alexandria?', 'multiple_choice', 35, 'https://images.unsplash.com/photo-1539650116574-75c0c6d4d129?w=400');

-- Insert Sample Questions for Quiz 4 (Programming)
INSERT INTO quiz_questions (quiz_id, question_text, question_type, time_limit, image_url) VALUES
(4, 'What does HTML stand for?', 'multiple_choice', 25, NULL),
(4, 'Which programming language is known for its use in data science?', 'multiple_choice', 30, NULL),
(4, 'What is the main purpose of CSS?', 'multiple_choice', 25, NULL);

-- Insert Sample Questions for Quiz 5 (Geography)
INSERT INTO quiz_questions (quiz_id, question_text, question_type, time_limit, image_url) VALUES
(5, 'What is the smallest country in the world?', 'multiple_choice', 25, NULL),
(5, 'Which river is the longest in the world?', 'multiple_choice', 30, NULL);

-- Insert Sample Answers for Quiz 1 (General Knowledge)
-- Question 1: Capital of France
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(1, 1, 'Paris', 1, NULL),
(1, 1, 'London', 0, NULL),
(1, 1, 'Berlin', 0, NULL),
(1, 1, 'Madrid', 0, NULL);

-- Question 2: Red Planet
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(1, 2, 'Mars', 1, NULL),
(1, 2, 'Venus', 0, NULL),
(1, 2, 'Jupiter', 0, NULL),
(1, 2, 'Saturn', 0, NULL);

-- Question 3: Largest mammal
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(1, 3, 'Blue Whale', 1, NULL),
(1, 3, 'Elephant', 0, NULL),
(1, 3, 'Giraffe', 0, NULL),
(1, 3, 'Hippopotamus', 0, NULL);

-- Question 4: WWII end
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(1, 4, '1945', 1, NULL),
(1, 4, '1944', 0, NULL),
(1, 4, '1946', 0, NULL),
(1, 4, '1943', 0, NULL);

-- Insert Sample Answers for Quiz 2 (Science & Technology)
-- Question 5: DNA
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(2, 5, 'Deoxyribonucleic Acid', 1, NULL),
(2, 5, 'Dynamic Network Access', 0, NULL),
(2, 5, 'Digital Network Architecture', 0, NULL),
(2, 5, 'Data Network Analysis', 0, NULL);

-- Question 6: Oxygen
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(2, 6, 'Oxygen', 1, NULL),
(2, 6, 'Gold', 0, NULL),
(2, 6, 'Silver', 0, NULL),
(2, 6, 'Osmium', 0, NULL);

-- Question 7: Speed of light
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(2, 7, '299,792,458 m/s', 1, NULL),
(2, 7, '300,000,000 m/s', 0, NULL),
(2, 7, '299,000,000 m/s', 0, NULL),
(2, 7, '298,792,458 m/s', 0, NULL);

-- Question 8: WWW inventor
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(2, 8, 'Tim Berners-Lee', 1, NULL),
(2, 8, 'Bill Gates', 0, NULL),
(2, 8, 'Steve Jobs', 0, NULL),
(2, 8, 'Mark Zuckerberg', 0, NULL);

-- Insert Sample Answers for Quiz 3 (History)
-- Question 9: First US President
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(3, 9, 'George Washington', 1, NULL),
(3, 9, 'John Adams', 0, NULL),
(3, 9, 'Thomas Jefferson', 0, NULL),
(3, 9, 'Benjamin Franklin', 0, NULL);

-- Question 10: Berlin Wall
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(3, 10, '1989', 1, NULL),
(3, 10, '1987', 0, NULL),
(3, 10, '1991', 0, NULL),
(3, 10, '1985', 0, NULL);

-- Question 11: Alexandria wonder
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(3, 11, 'Lighthouse of Alexandria', 1, NULL),
(3, 11, 'Great Pyramid of Giza', 0, NULL),
(3, 11, 'Colossus of Rhodes', 0, NULL),
(3, 11, 'Hanging Gardens of Babylon', 0, NULL);

-- Insert Sample Answers for Quiz 4 (Programming)
-- Question 12: HTML
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(4, 12, 'HyperText Markup Language', 1, NULL),
(4, 12, 'High Tech Modern Language', 0, NULL),
(4, 12, 'Home Tool Markup Language', 0, NULL),
(4, 12, 'Hyperlink and Text Markup Language', 0, NULL);

-- Question 13: Data science language
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(4, 13, 'Python', 1, NULL),
(4, 13, 'HTML', 0, NULL),
(4, 13, 'CSS', 0, NULL),
(4, 13, 'Assembly', 0, NULL);

-- Question 14: CSS purpose
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(4, 14, 'Styling web pages', 1, NULL),
(4, 14, 'Database management', 0, NULL),
(4, 14, 'Server-side scripting', 0, NULL),
(4, 14, 'Network security', 0, NULL);

-- Insert Sample Answers for Quiz 5 (Geography)
-- Question 15: Smallest country
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(5, 15, 'Vatican City', 1, NULL),
(5, 15, 'Monaco', 0, NULL),
(5, 15, 'San Marino', 0, NULL),
(5, 15, 'Liechtenstein', 0, NULL);

-- Question 16: Longest river
INSERT INTO answers (quiz_id, question_id, answer_text, is_correct, image_url) VALUES
(5, 16, 'Nile River', 1, NULL),
(5, 16, 'Amazon River', 0, NULL),
(5, 16, 'Mississippi River', 0, NULL),
(5, 16, 'Yangtze River', 0, NULL);

-- ==============================================================================
-- VERIFICATION QUERIES
-- ==============================================================================

-- Display summary of inserted data
SELECT '=== QUIZ API DATABASE SETUP COMPLETE ===' as status;
SELECT '' as '';
SELECT 'DATA SUMMARY:' as info;
SELECT COUNT(*) as total_quizzes FROM quizes;
SELECT COUNT(*) as total_questions FROM quiz_questions;
SELECT COUNT(*) as total_answers FROM answers;
SELECT '' as '';

SELECT 'QUIZ BREAKDOWN:' as info;
SELECT 
    q.id, 
    q.title, 
    q.category, 
    COUNT(qq.id) as question_count,
    COUNT(a.id) as answer_count
FROM quizes q 
LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id 
LEFT JOIN answers a ON q.id = a.quiz_id
GROUP BY q.id, q.title, q.category
ORDER BY q.id;

SELECT '' as '';
SELECT 'SETUP STATUS: ✅ SUCCESS - Quiz API database ready!' as final_status;
