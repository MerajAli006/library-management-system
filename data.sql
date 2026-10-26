USE library_db;

-- Categories
INSERT INTO category (category_name) VALUES 
('Computer Science'), ('Mathematics'), ('Physics'), ('Literature'), ('Management');

-- Staff (Default login: admin / admin123)
INSERT INTO staff (name, username, password_hash) VALUES 
('Meraj Ali', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 20 Books (Book 20 has 0 available copies for testing)
INSERT INTO book (isbn, title, author, publisher, pub_year, category_id, total_copies, available_copies) VALUES
('978-0131103627', 'The C Programming Language', 'Brian Kernighan', 'Prentice Hall', 1988, 1, 5, 4),
('978-0262033848', 'Introduction to Algorithms', 'Thomas H. Cormen', 'MIT Press', 2009, 1, 4, 3),
('978-0132350884', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 1, 3, 2),
('978-0201633610', 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 1, 3, 2),
('978-0073523323', 'Database System Concepts', 'Abraham Silberschatz', 'McGraw-Hill', 2010, 1, 6, 5),
('978-0133594140', 'Computer Networking', 'James Kurose', 'Pearson', 2016, 1, 4, 3),
('978-1118063330', 'Operating System Concepts', 'Abraham Silberschatz', 'Wiley', 2012, 1, 5, 4),
('978-0136042594', 'Artificial Intelligence', 'Stuart Russell', 'Pearson', 2009, 1, 3, 3),
('978-0486653556', 'Linear Algebra and Its Applications', 'Gilbert Strang', 'Cengage', 2006, 2, 4, 3),
('978-0073383095', 'Discrete Mathematics', 'Kenneth Rosen', 'McGraw-Hill', 2011, 2, 5, 4),
('978-0521880688', 'Numerical Recipes', 'William H. Press', 'Cambridge Press', 2007, 2, 2, 2),
('978-1285741550', 'Calculus: Early Transcendentals', 'James Stewart', 'Cengage', 2015, 2, 4, 4),
('978-0471804580', 'Fundamentals of Physics', 'David Halliday', 'Wiley', 2013, 3, 5, 4),
('978-0521198110', 'Introduction to Electrodynamics', 'David J. Griffiths', 'Cambridge Press', 2012, 3, 3, 3),
('978-0198520115', 'Quantum Mechanics', 'Claude Cohen', 'Oxford Press', 1991, 3, 2, 2),
('978-0140449136', 'The Odyssey', 'Homer', 'Penguin Classics', 2003, 4, 3, 3),
('978-0451524935', '1984', 'George Orwell', 'Signet Classic', 1950, 4, 4, 4),
('978-0061120084', 'To Kill a Mockingbird', 'Harper Lee', 'Harper Perennial', 2006, 4, 3, 3),
('978-0132156622', 'Marketing Management', 'Philip Kotler', 'Pearson', 2011, 5, 4, 4),
('978-0078029547', 'Organizational Behavior', 'Stephen P. Robbins', 'Pearson', 2014, 5, 1, 0);

-- 10 Members (Member 1 holds 3 books; Member 8 is Suspended)
INSERT INTO member (name, email, phone, member_type, join_date, status) VALUES
('Aarav Verma', 'aarav@college.edu', '9876543210', 'Student', '2025-07-10', 'Active'),
('Priya Nair', 'priya@college.edu', '9876543211', 'Student', '2025-07-12', 'Active'),
('Rohan Gupta', 'rohan@college.edu', '9876543212', 'Student', '2025-08-01', 'Active'),
('Sneha Patel', 'sneha@college.edu', '9876543213', 'Student', '2025-08-15', 'Active'),
('Karan Mehta', 'karan@college.edu', '9876543214', 'Student', '2025-09-01', 'Active'),
('Ananya Joshi', 'ananya@college.edu', '9876543215', 'Student', '2025-09-10', 'Active'),
('Vikram Singh', 'vikram@college.edu', '9876543216', 'Student', '2025-09-20', 'Active'),
('Neha Reddy', 'neha@college.edu', '9876543217', 'Student', '2025-10-01', 'Suspended'),
('Dr. Suresh Iyer', 'suresh.iyer@college.edu', '9876543218', 'Faculty', '2024-01-15', 'Active'),
('Prof. Kavita Rao', 'kavita.rao@college.edu', '9876543219', 'Faculty', '2024-03-20', 'Active');

-- 15 Issue Records (Mix of returned on time, returned late, currently active, and currently overdue)
INSERT INTO issue (book_id, member_id, staff_id, issue_date, due_date, return_date) VALUES
-- Member 1 (Aarav - Student) already holds 3 books (for testing max limit of 3)
(1, 1, 1, DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 9 DAY), NULL),
(2, 1, 1, DATE_SUB(CURRENT_DATE, INTERVAL 4 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY), NULL),
(3, 1, 1, DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 11 DAY), NULL),
-- Overdue unreturned issues (including 5 days late for test case)
(4, 2, 1, DATE_SUB(CURRENT_DATE, INTERVAL 19 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 5 DAY), NULL),
(5, 3, 1, DATE_SUB(CURRENT_DATE, INTERVAL 22 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 8 DAY), NULL),
(6, 4, 1, DATE_SUB(CURRENT_DATE, INTERVAL 17 DAY), DATE_SUB(CURRENT_DATE, INTERVAL 3 DAY), NULL),
-- Active within due date
(7, 5, 1, DATE_SUB(CURRENT_DATE, INTERVAL 6 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 8 DAY), NULL),
(9, 9, 1, DATE_SUB(CURRENT_DATE, INTERVAL 10 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 4 DAY), NULL),
(10, 10, 1, DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 12 DAY), NULL),
(13, 6, 1, DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 13 DAY), NULL),
(20, 9, 1, DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY), DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), NULL),
-- Already Returned on time
(8, 2, 1, '2026-01-10', '2026-01-24', '2026-01-20'),
(11, 3, 1, '2026-01-12', '2026-01-26', '2026-01-25'),
-- Already Returned Late (creates fine records below)
(12, 4, 1, '2026-01-01', '2026-01-15', '2026-01-20'),
(14, 5, 1, '2026-01-05', '2026-01-19', '2026-01-23');

-- Sample Fines for the 2 late returned books
INSERT INTO fine (issue_id, amount, paid_status, paid_date) VALUES
(14, 10.00, 'Paid', '2026-01-20'),
(15, 8.00, 'Unpaid', NULL);