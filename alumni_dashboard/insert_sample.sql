USE alumni_system;

INSERT INTO users (name, email, password_hash, role) VALUES ('Niksur Babia Dagandan', 'bniksur@gmail.com', MD5('password'), 'alumni');

INSERT INTO pds_contact (user_id, first_name, last_name, middle_name, age, gender, civil_status, religion, birth_date, blood_type, course, course_code, university, year_graduated, phone_number, email) VALUES (LAST_INSERT_ID(), 'Niksur', 'Babia', 'Dagandan', 25, 'Male', 'Single', 'Christian', '1999-01-01', 'O+', 'Computer Science', 'CS101', 'USTP', 2023, '1234567890', 'bniksur@gmail.com');
