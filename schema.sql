CREATE DATABASE IF NOT EXISTS library_db;
USE library_db;

-- 1. Category Table
CREATE TABLE category (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL UNIQUE
);

-- 2. Book Table
CREATE TABLE book (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    publisher VARCHAR(150) NOT NULL,
    pub_year INT NOT NULL,
    category_id INT NOT NULL,
    total_copies INT NOT NULL CHECK (total_copies >= 0),
    available_copies INT NOT NULL CHECK (available_copies >= 0),
    FOREIGN KEY (category_id) REFERENCES category(category_id) ON DELETE RESTRICT
);

-- 3. Member Table
CREATE TABLE member (
    member_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(15) NOT NULL,
    member_type ENUM('Student', 'Faculty') NOT NULL DEFAULT 'Student',
    join_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    status ENUM('Active', 'Suspended') NOT NULL DEFAULT 'Active'
);

-- 4. Staff Table
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

-- 5. Issue Table
CREATE TABLE issue (
    issue_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    staff_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    FOREIGN KEY (book_id) REFERENCES book(book_id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES member(member_id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
);

-- 6. Fine Table
CREATE TABLE fine (
    fine_id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL UNIQUE,
    amount DECIMAL(10,2) NOT NULL CHECK (amount >= 0),
    paid_status ENUM('Paid', 'Unpaid') NOT NULL DEFAULT 'Unpaid',
    paid_date DATE DEFAULT NULL,
    FOREIGN KEY (issue_id) REFERENCES issue(issue_id) ON DELETE CASCADE
);

-- View 1: Currently Issued Books
CREATE OR REPLACE VIEW v_issued_books AS
SELECT 
    i.issue_id, b.book_id, b.isbn, b.title, m.member_id, m.name AS member_name, 
    m.member_type, i.issue_date, i.due_date
FROM issue i
JOIN book b ON i.book_id = b.book_id
JOIN member m ON i.member_id = m.member_id
WHERE i.return_date IS NULL;

-- View 2: Overdue Books
CREATE OR REPLACE VIEW v_overdue_books AS
SELECT 
    i.issue_id, b.title, m.name AS member_name, m.phone, i.issue_date, i.due_date,
    DATEDIFF(CURRENT_DATE, i.due_date) AS days_overdue,
    (DATEDIFF(CURRENT_DATE, i.due_date) * 2) AS estimated_fine
FROM issue i
JOIN book b ON i.book_id = b.book_id
JOIN member m ON i.member_id = m.member_id
WHERE i.return_date IS NULL AND CURRENT_DATE > i.due_date;