-- Create and use the database
CREATE DATABASE IF NOT EXISTS jainz_matrimony;
USE jainz_matrimony;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    verification_token VARCHAR(64),
    email_verified TINYINT(1) DEFAULT 0,
1	id Primary	int(11)			No	None		AUTO_INCREMENT	Change Change	Drop Drop	
	2	name	varchar(100)	utf8mb4_general_ci		No	None			Change Change	Drop Drop	
	3	email Index	varchar(100)	utf8mb4_general_ci		No	None			Change Change	Drop Drop	
	4	password	varchar(255)	utf8mb4_general_ci		No	None			Change Change	Drop Drop	
	5	birth_date	date			No	None			Change Change	Drop Drop	
	6	gender Index	enum('male', 'female')	utf8mb4_general_ci		No	None			Change Change	Drop Drop	
	7	verification_token Index	varchar(64)	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	8	email_verified	tinyint(1)			Yes	0			Change Change	Drop Drop	
	9	status Index	enum('active', 'inactive', 'suspended')	utf8mb4_general_ci		Yes	inactive			Change Change	Drop Drop	
	10	created_at	timestamp			No	current_timestamp()			Change Change	Drop Drop	
	11	updated_at	timestamp			No	current_timestamp()		ON UPDATE CURRENT_TIMESTAMP()	Change Change	Drop Drop	
	12	fun_fact	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	13	meaning_of_jain	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	14	looking_for	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	15	diet	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	16	babies	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	17	toxic_trait	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	18	vacation_spots	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	19	carousel_images	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	20	education	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	21	profile_picture	varchar(255)	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
	22	profession	text	utf8mb4_general_ci		Yes	NULL			Change Change	Drop Drop	
With selected:  



-- Table for storing conversations between users
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_1 INT NOT NULL,
    user_id_2 INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_1) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_conversation (user_id_1, user_id_2),
    INDEX (user_id_1),
    INDEX (user_id_2)
);

-- Table for storing messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (conversation_id),
    INDEX (sender_id)
);
-- This assumes you already have a users table with the following columns:
-- id, name, email, password, birth_date, gender, verification_token, email_verified, 
-- status, created_at, fun_fact, updated_at, meaning_of_jain, looking_for, diet, 
-- babies, toxic_trait, vacation_spots

-- Table for storing carousel images


-- Table for storing user actions (likes/dislikes)
CREATE TABLE IF NOT EXISTS user_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    profile_id INT NOT NULL,
    action ENUM('like', 'dislike') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (profile_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_action (user_id, profile_id),
    INDEX (user_id),
    INDEX (profile_id)
);

-- Table for storing matches
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id_1 INT NOT NULL,
    user_id_2 INT NOT NULL,
    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id_1) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id_2) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (user_id_1, user_id_2),
    INDEX (user_id_1),
    INDEX (user_id_2)
);

-- Table for storing additional prompt answers if needed
