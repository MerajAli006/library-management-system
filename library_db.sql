-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 09:16 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `log_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `action_timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`log_id`, `action_type`, `description`, `action_timestamp`) VALUES
(1, 'SYSTEM_UPGRADE', 'Database upgraded to Enterprise v2.0 with M:N Author mapping, Triggers & Reservation Queue', '2026-10-27 00:01:06'),
(2, 'BOOK_ISSUED', 'Book ID #8 issued to Member ID #11 (Due: 2026-11-10)', '2026-10-27 00:56:48'),
(3, 'BOOK_RETURNED', 'Issue ID #21 (Book ID #4) returned on 2026-10-27', '2026-10-27 00:57:01'),
(4, 'BOOK_RETURNED', 'Issue ID #6 (Book ID #6) returned on 2026-10-27', '2026-10-27 00:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `author`
--

CREATE TABLE `author` (
  `author_id` int(11) NOT NULL,
  `author_name` varchar(150) NOT NULL,
  `bio` varchar(255) DEFAULT 'Academic Author'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `author`
--

INSERT INTO `author` (`author_id`, `author_name`, `bio`) VALUES
(1, 'Brian Kernighan', 'Academic Author'),
(2, 'Thomas H. Cormen', 'Academic Author'),
(3, 'Robert C. Martin', 'Academic Author'),
(4, 'Erich Gamma', 'Academic Author'),
(5, 'Abraham Silberschatz', 'Academic Author'),
(6, 'James Kurose', 'Academic Author'),
(7, 'Stuart Russell', 'Academic Author'),
(8, 'Gilbert Strang', 'Academic Author'),
(9, 'Kenneth Rosen', 'Academic Author'),
(10, 'William H. Press', 'Academic Author'),
(11, 'James Stewart', 'Academic Author'),
(12, 'David Halliday', 'Academic Author'),
(13, 'David J. Griffiths', 'Academic Author'),
(14, 'Claude Cohen', 'Academic Author'),
(15, 'Homer', 'Academic Author'),
(16, 'George Orwell', 'Academic Author'),
(17, 'Harper Lee', 'Academic Author'),
(18, 'Philip Kotler', 'Academic Author'),
(19, 'Stephen P. Robbins', 'Academic Author');

-- --------------------------------------------------------

--
-- Table structure for table `book`
--

CREATE TABLE `book` (
  `book_id` int(11) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `publisher` varchar(150) NOT NULL,
  `pub_year` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `total_copies` int(11) NOT NULL CHECK (`total_copies` >= 0),
  `available_copies` int(11) NOT NULL CHECK (`available_copies` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book`
--

INSERT INTO `book` (`book_id`, `isbn`, `title`, `author`, `publisher`, `pub_year`, `category_id`, `total_copies`, `available_copies`) VALUES
(1, '978-0131103627', 'The C Programming Language', 'Brian Kernighan', 'Prentice Hall', 1988, 1, 5, 4),
(2, '978-0262033848', 'Introduction to Algorithms', 'Thomas H. Cormen', 'MIT Press', 2009, 1, 4, 3),
(3, '978-0132350884', 'Clean Code', 'Robert C. Martin', 'Prentice Hall', 2008, 1, 3, 3),
(4, '978-0201633610', 'Design Patterns', 'Erich Gamma', 'Addison-Wesley', 1994, 1, 3, 3),
(5, '978-0073523323', 'Database System Concepts', 'Abraham Silberschatz', 'McGraw-Hill', 2010, 1, 6, 5),
(6, '978-0133594140', 'Computer Networking', 'James Kurose', 'Pearson', 2016, 1, 4, 4),
(7, '978-1118063330', 'Operating System Concepts', 'Abraham Silberschatz', 'Wiley', 2012, 1, 5, 4),
(8, '978-0136042594', 'Artificial Intelligence', 'Stuart Russell', 'Pearson', 2009, 1, 3, 2),
(9, '978-0486653556', 'Linear Algebra and Its Applications', 'Gilbert Strang', 'Cengage', 2006, 2, 4, 3),
(10, '978-0073383095', 'Discrete Mathematics', 'Kenneth Rosen', 'McGraw-Hill', 2011, 2, 5, 4),
(11, '978-0521880688', 'Numerical Recipes', 'William H. Press', 'Cambridge Press', 2007, 2, 2, 2),
(12, '978-1285741550', 'Calculus: Early Transcendentals', 'James Stewart', 'Cengage', 2015, 2, 4, 4),
(13, '978-0471804580', 'Fundamentals of Physics', 'David Halliday', 'Wiley', 2013, 3, 5, 5),
(14, '978-0521198110', 'Introduction to Electrodynamics', 'David J. Griffiths', 'Cambridge Press', 2012, 3, 3, 3),
(15, '978-0198520115', 'Quantum Mechanics', 'Claude Cohen', 'Oxford Press', 1991, 3, 2, 2),
(16, '978-0140449136', 'The Odyssey', 'Homer', 'Penguin Classics', 2003, 4, 3, 3),
(17, '978-0451524935', '1984', 'George Orwell', 'Signet Classic', 1950, 4, 4, 2),
(18, '978-0061120084', 'To Kill a Mockingbird', 'Harper Lee', 'Harper Perennial', 2006, 4, 3, 3),
(19, '978-0132156622', 'Marketing Management', 'Philip Kotler', 'Pearson', 2011, 5, 4, 4),
(20, '978-0078029547', 'Organizational Behavior', 'Stephen P. Robbins', 'Pearson', 2014, 5, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `book_author`
--

CREATE TABLE `book_author` (
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_author`
--

INSERT INTO `book_author` (`book_id`, `author_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 6),
(7, 5),
(8, 7),
(9, 8),
(10, 9),
(11, 10),
(12, 11),
(13, 12),
(14, 13),
(15, 14),
(16, 15),
(17, 16),
(18, 17),
(19, 18),
(20, 19);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`category_id`, `category_name`) VALUES
(1, 'Computer Science'),
(4, 'Literature'),
(5, 'Management'),
(2, 'Mathematics'),
(3, 'Physics');

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `fine_id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL CHECK (`amount` >= 0),
  `paid_status` enum('Paid','Unpaid') NOT NULL DEFAULT 'Unpaid',
  `paid_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine`
--

INSERT INTO `fine` (`fine_id`, `issue_id`, `amount`, `paid_status`, `paid_date`) VALUES
(1, 14, 10.00, 'Paid', '2026-01-20'),
(2, 15, 8.00, 'Unpaid', NULL),
(3, 4, 10.00, 'Paid', '2026-10-26'),
(4, 6, 8.00, 'Unpaid', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `issue`
--

CREATE TABLE `issue` (
  `issue_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `issue`
--

INSERT INTO `issue` (`issue_id`, `book_id`, `member_id`, `staff_id`, `issue_date`, `due_date`, `return_date`) VALUES
(1, 1, 1, 1, '2026-10-21', '2026-11-04', NULL),
(2, 2, 1, 1, '2026-10-22', '2026-11-05', NULL),
(3, 3, 1, 1, '2026-10-23', '2026-11-06', '2026-10-26'),
(4, 4, 2, 1, '2026-10-07', '2026-10-21', '2026-10-26'),
(5, 5, 3, 1, '2026-10-04', '2026-10-18', NULL),
(6, 6, 4, 1, '2026-10-09', '2026-10-23', '2026-10-27'),
(7, 7, 5, 1, '2026-10-20', '2026-11-03', '2026-10-26'),
(8, 9, 9, 1, '2026-10-16', '2026-10-30', NULL),
(9, 10, 10, 1, '2026-10-24', '2026-11-07', NULL),
(10, 13, 6, 1, '2026-10-25', '2026-11-08', '2026-10-26'),
(11, 20, 9, 1, '2026-10-19', '2026-11-02', NULL),
(12, 8, 2, 1, '2026-01-10', '2026-01-24', '2026-01-20'),
(13, 11, 3, 1, '2026-01-12', '2026-01-26', '2026-01-25'),
(14, 12, 4, 1, '2026-01-01', '2026-01-15', '2026-01-20'),
(15, 14, 5, 1, '2026-01-05', '2026-01-19', '2026-01-23'),
(16, 5, 5, 1, '2026-10-26', '2026-11-09', '2026-10-26'),
(17, 7, 3, 1, '2026-10-26', '2026-11-09', NULL),
(18, 17, 9, 1, '2026-10-26', '2026-11-09', NULL),
(19, 17, 2, 1, '2026-10-26', '2026-11-09', '2026-10-26'),
(20, 17, 1, 1, '2026-10-26', '2026-11-09', NULL),
(21, 4, 2, 1, '2026-10-26', '2026-11-09', '2026-10-27'),
(22, 8, 11, 1, '2026-10-27', '2026-11-10', NULL);

--
-- Triggers `issue`
--
DELIMITER $$
CREATE TRIGGER `trg_after_book_issue` AFTER INSERT ON `issue` FOR EACH ROW BEGIN
    INSERT INTO audit_log (action_type, description)
    VALUES ('BOOK_ISSUED', CONCAT('Book ID #', NEW.book_id, ' issued to Member ID #', NEW.member_id, ' (Due: ', NEW.due_date, ')'));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_book_return` AFTER UPDATE ON `issue` FOR EACH ROW BEGIN
    IF OLD.return_date IS NULL AND NEW.return_date IS NOT NULL THEN
        INSERT INTO audit_log (action_type, description)
        VALUES ('BOOK_RETURNED', CONCAT('Issue ID #', NEW.issue_id, ' (Book ID #', NEW.book_id, ') returned on ', NEW.return_date));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `member_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `member_type` enum('Student','Faculty') NOT NULL DEFAULT 'Student',
  `join_date` date NOT NULL DEFAULT curdate(),
  `status` enum('Active','Suspended') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`member_id`, `name`, `email`, `phone`, `member_type`, `join_date`, `status`) VALUES
(1, 'Aarav Verma', 'aarav@college.edu', '9876543210', 'Student', '2025-07-10', 'Active'),
(2, 'Priya Nair', 'priya@college.edu', '9876543211', 'Student', '2025-07-12', 'Active'),
(3, 'Rohan Gupta', 'rohan@college.edu', '9876543212', 'Student', '2025-08-01', 'Active'),
(4, 'Sneha Patel', 'sneha@college.edu', '9876543213', 'Student', '2025-08-15', 'Active'),
(5, 'Karan Mehta', 'karan@college.edu', '9876543214', 'Student', '2025-09-01', 'Active'),
(6, 'Ananya Joshi', 'ananya@college.edu', '9876543215', 'Student', '2025-09-10', 'Active'),
(7, 'Vikram Singh', 'vikram@college.edu', '9876543216', 'Student', '2025-09-20', 'Active'),
(8, 'Neha Reddy', 'neha@college.edu', '9876543217', 'Student', '2025-10-01', 'Suspended'),
(9, 'Dr. Suresh Iyer', 'suresh.iyer@college.edu', '9876543218', 'Faculty', '2024-01-15', 'Active'),
(10, 'Prof. Kavita Rao', 'kavita.rao@college.edu', '9876543219', 'Faculty', '2024-03-20', 'Active'),
(11, 'Meraj Ali', 'merajali250506@gmail.com', '9616850215', 'Student', '2026-10-27', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `request_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Pending','Fulfilled','Cancelled') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `book_id`, `member_id`, `request_date`, `status`) VALUES
(1, 20, 2, '2026-10-27 01:04:23', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `name`, `username`, `password_hash`) VALUES
(1, 'Meraj Ali', 'admin', '$2y$12$cL4rDoXoxJ0/6Olx/kZyI.DSbwyFbkvivH26ZixJ6W3xsIxsunwLm');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_issued_books`
-- (See below for the actual view)
--
CREATE TABLE `v_issued_books` (
`issue_id` int(11)
,`book_id` int(11)
,`isbn` varchar(20)
,`title` varchar(200)
,`member_id` int(11)
,`member_name` varchar(150)
,`member_type` enum('Student','Faculty')
,`issue_date` date
,`due_date` date
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_overdue_books`
-- (See below for the actual view)
--
CREATE TABLE `v_overdue_books` (
`issue_id` int(11)
,`title` varchar(200)
,`member_name` varchar(150)
,`phone` varchar(15)
,`issue_date` date
,`due_date` date
,`days_overdue` int(8)
,`estimated_fine` int(9)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `author`
--
ALTER TABLE `author`
  ADD PRIMARY KEY (`author_id`),
  ADD UNIQUE KEY `author_name` (`author_name`);

--
-- Indexes for table `book`
--
ALTER TABLE `book`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `book_author`
--
ALTER TABLE `book_author`
  ADD PRIMARY KEY (`book_id`,`author_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD PRIMARY KEY (`fine_id`),
  ADD UNIQUE KEY `issue_id` (`issue_id`);

--
-- Indexes for table `issue`
--
ALTER TABLE `issue`
  ADD PRIMARY KEY (`issue_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`member_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `author`
--
ALTER TABLE `author`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `book`
--
ALTER TABLE `book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fine`
--
ALTER TABLE `fine`
  MODIFY `fine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `issue`
--
ALTER TABLE `issue`
  MODIFY `issue_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure for view `v_issued_books`
--
DROP TABLE IF EXISTS `v_issued_books`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_issued_books`  AS SELECT `i`.`issue_id` AS `issue_id`, `b`.`book_id` AS `book_id`, `b`.`isbn` AS `isbn`, `b`.`title` AS `title`, `m`.`member_id` AS `member_id`, `m`.`name` AS `member_name`, `m`.`member_type` AS `member_type`, `i`.`issue_date` AS `issue_date`, `i`.`due_date` AS `due_date` FROM ((`issue` `i` join `book` `b` on(`i`.`book_id` = `b`.`book_id`)) join `member` `m` on(`i`.`member_id` = `m`.`member_id`)) WHERE `i`.`return_date` is null ;

-- --------------------------------------------------------

--
-- Structure for view `v_overdue_books`
--
DROP TABLE IF EXISTS `v_overdue_books`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_overdue_books`  AS SELECT `i`.`issue_id` AS `issue_id`, `b`.`title` AS `title`, `m`.`name` AS `member_name`, `m`.`phone` AS `phone`, `i`.`issue_date` AS `issue_date`, `i`.`due_date` AS `due_date`, to_days(curdate()) - to_days(`i`.`due_date`) AS `days_overdue`, (to_days(curdate()) - to_days(`i`.`due_date`)) * 2 AS `estimated_fine` FROM ((`issue` `i` join `book` `b` on(`i`.`book_id` = `b`.`book_id`)) join `member` `m` on(`i`.`member_id` = `m`.`member_id`)) WHERE `i`.`return_date` is null AND curdate() > `i`.`due_date` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book`
--
ALTER TABLE `book`
  ADD CONSTRAINT `book_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`category_id`);

--
-- Constraints for table `book_author`
--
ALTER TABLE `book_author`
  ADD CONSTRAINT `book_author_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `book_author_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `author` (`author_id`) ON DELETE CASCADE;

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `issue` (`issue_id`) ON DELETE CASCADE;

--
-- Constraints for table `issue`
--
ALTER TABLE `issue`
  ADD CONSTRAINT `issue_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issue_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`staff_id`) ON DELETE CASCADE;

--
-- Constraints for table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `reservation_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `book` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservation_ibfk_2` FOREIGN KEY (`member_id`) REFERENCES `member` (`member_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
