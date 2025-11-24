CREATE DATABASE IF NOT EXISTS alumni_system;
USE alumni_system;

-- Alumni System Database Schema

-- 1. Users Table (Main Account & Authentication)
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('alumni', 'admin') DEFAULT 'alumni',
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CHECK (email LIKE '%@%')
) ENGINE=InnoDB;
CREATE INDEX idx_user_email ON users(email);

-- 2. PDS_CONTACT Table (Personal Data Sheet Contact Info)
DROP TABLE IF EXISTS pds_contact;
CREATE TABLE pds_contact (
    pds_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    extension_name VARCHAR(10),
    nick_name VARCHAR(50),
    age INT NOT NULL,
    gender VARCHAR(10) NOT NULL,
    civil_status VARCHAR(20) NOT NULL,
    religion VARCHAR(50) NOT NULL,
    birth_date DATE NOT NULL,
    blood_type VARCHAR(50) NOT NULL,
    course VARCHAR(100) NOT NULL,
    course_code VARCHAR(20) NOT NULL,
    university VARCHAR(100) NOT NULL,
    year_graduated INT NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    CHECK (age > 0),
    CHECK (email LIKE '%@%')
) ENGINE=InnoDB;
CREATE INDEX idx_pds_user_id ON pds_contact(user_id);

-- 3. HOME_ADDRESS Table
DROP TABLE IF EXISTS home_address;
CREATE TABLE home_address (
    address_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    house_number VARCHAR(50),
    street VARCHAR(100),
    barangay VARCHAR(100) NOT NULL,
    city_municipal VARCHAR(100) NOT NULL,
    province VARCHAR(100) NOT NULL,
    zip_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    region VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_home_user_id ON home_address(user_id);

-- 4. PARENT_LEGAL_GUARDIAN Table
DROP TABLE IF EXISTS parent_legal_guardian;
CREATE TABLE parent_legal_guardian (
    parent_guardian_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    father_name VARCHAR(100),
    father_occupation VARCHAR(100),
    father_contact_number VARCHAR(20),
    mother_name VARCHAR(100),
    mother_occupation VARCHAR(100),
    mother_contact_number VARCHAR(20),
    guardian_name VARCHAR(100),
    guardian_occupation VARCHAR(100),
    guardian_contact_number VARCHAR(20),
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_parent_user_id ON parent_legal_guardian(user_id);

-- 5. EMERGENCY_CONTACT Table
DROP TABLE IF EXISTS emergency_contact;
CREATE TABLE emergency_contact (
    emergency_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    contact_name VARCHAR(100) NOT NULL,
    relationship VARCHAR(50) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    contact_address VARCHAR(255) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_emergency_user_id ON emergency_contact(user_id);

-- 6. WORK_EXPERIENCE Table (One-to-Many relationship for multiple jobs)
DROP TABLE IF EXISTS work_experience;
CREATE TABLE work_experience (
    work_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- One-to-Many relationship
    organization_business_name VARCHAR(100) NOT NULL,
    owner_proprietor_name VARCHAR(100),
    position VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_work_user_id ON work_experience(user_id);

-- 7. USER_SKILLS Table (Normalized structure replacing the 8-column TALENTS_SKILLS)
DROP TABLE IF EXISTS user_skills;
CREATE TABLE user_skills (
    skill_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL, -- One-to-Many relationship
    skill_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_skills_user_id ON user_skills(user_id);

-- 8. GRADUATION_MESSAGE Table
DROP TABLE IF EXISTS graduation_message;
CREATE TABLE graduation_message (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    message TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_message_user_id ON graduation_message(user_id);

-- 9. ADDITIONAL_INFORMATION Table (Handles signature/control details)
DROP TABLE IF EXISTS additional_information;
CREATE TABLE additional_information (
    info_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE, -- One-to-One relationship
    user_signature_path VARCHAR(255) NOT NULL, -- Stores file path or link to the signature
    date_accomplished DATE NOT NULL,
    control_number VARCHAR(50) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
) ENGINE=InnoDB;
CREATE INDEX idx_info_user_id ON additional_information(user_id);

-- 10. EVENTS Table (For admin-created events)
DROP TABLE IF EXISTS events;
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    organizer VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_event_date ON events(date);

-- 11. ANNOUNCEMENTS Table (For admin-created announcements)
DROP TABLE IF EXISTS announcements;
CREATE TABLE announcements (
    announcement_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE INDEX idx_announcement_date ON announcements(date);

-- Insert default admin user
INSERT INTO users (name, email, password_hash, role) VALUES ('Admin', 'admin@gmail.com', 'admin123', 'admin');
