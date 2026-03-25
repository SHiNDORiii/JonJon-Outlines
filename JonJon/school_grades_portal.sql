-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 25, 2026 at 07:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `school_grades_portal`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `email`, `password`, `first_name`, `last_name`) VALUES
(1, 'admin@admin.edu', 'admin123', 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `grade`
--

CREATE TABLE `grade` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` varchar(20) DEFAULT NULL,
  `prelim_grade` decimal(5,2) DEFAULT NULL,
  `midterm_grade` decimal(5,2) DEFAULT NULL,
  `final_grade` decimal(5,2) DEFAULT NULL,
  `average_grade` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grade`
--

INSERT INTO `grade` (`grade_id`, `student_id`, `subject_id`, `teacher_id`, `school_year`, `semester`, `prelim_grade`, `midterm_grade`, `final_grade`, `average_grade`) VALUES
(3, 3, 1, 1, '2025-2026', '2ND', 89.00, 85.00, 87.00, 87.00),
(4, 3, 2, 2, '2025-2026', '2ND', 75.00, 88.00, 92.00, 85.00),
(5, 3, 3, 3, '2025-2026', '2ND', 89.00, 85.00, 87.00, 87.00),
(6, 3, 4, 4, '2025-2026', '2ND', 92.00, 89.00, 91.00, 90.60),
(7, 3, 6, 5, '2025-2026', '2ND', 95.00, 91.00, 92.00, 92.60),
(8, 3, 5, 6, '2025-2026', '2ND', 78.00, 83.00, 82.00, 81.00),
(9, 4, 2, 2, '2025-2026', '2ND', 78.00, 75.00, 74.00, 75.60),
(10, 4, 1, 1, '2025-2026', '2ND', 86.00, 85.00, 86.00, 85.60),
(11, 4, 3, 3, '2025-2026', '2ND', 86.00, 89.00, 87.00, 87.30),
(12, 4, 4, 4, '2025-2026', '2ND', 95.00, 88.00, 92.00, 92.60),
(13, 4, 5, 5, '2025-2026', '2ND', 84.00, 81.00, 77.00, 80.60),
(14, 4, 6, 6, '2025-2026', '2ND', 89.00, 85.00, 88.00, 87.30);

-- --------------------------------------------------------

--
-- Table structure for table `registrar`
--

CREATE TABLE `registrar` (
  `registrar_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `office_role` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrar`
--

INSERT INTO `registrar` (`registrar_id`, `email`, `password`, `first_name`, `last_name`, `office_role`) VALUES
(1, 'maria.ds@email.com', 'maria123', 'Maria', 'Delos Santos', 'Enrollment');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `year_level` varchar(10) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `student_number`, `email`, `password`, `first_name`, `last_name`, `year_level`, `section`, `course`) VALUES
(3, 'S-0002', 'ranielbuan@gmail.com', '$2y$10$vcQrapCmHw.9BXiW6RedJOfvqlg6dGH0PnVqV/zJd7x2wQNiz/gTO', 'Jon Raniel', 'Buan', '2nd Year', '2A', 'Bachelor of Science in Information Technology'),
(4, 'S-0003', 'k.silvestre@gmail.com', '$2y$10$vcQrapCmHw.9BXiW6RedJOfvqlg6dGH0PnVqV/zJd7x2wQNiz/gTO', 'Kniger', 'Silvestre', '1', '1B', 'BSIT');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `units` int(11) DEFAULT 1,
  `teacher_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_code`, `subject_name`, `description`, `units`, `teacher_id`) VALUES
(1, 'WD0001', 'Web Development 1', 'erm', 3, 1),
(2, 'DM0001', 'Discrete Math', 'erm', 3, 2),
(3, 'PFIT0002', 'PFIT 2', 'erm', 2, 3),
(4, 'HCI0001', 'Human and Computer Interactions', 'dfnjdsk', 4, 4),
(5, 'MMW0001', 'Mathematics in the Modern World', 'adgdtj', 5, 5),
(6, 'ITE0001', 'Living in the IT Era', 'sdhdfjds', 5, 6);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `teacher_code` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `department` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `teacher_code`, `email`, `password`, `first_name`, `last_name`, `department`) VALUES
(1, 'SMACP1', 'joseph.cris@gmail.com', 'otep123', 'Joseph Cris', 'Valdez', 'SCITE'),
(2, 'SMACP2', 'Juan@email.com', '123', 'Juan', 'Dela Crus', 'SCITE'),
(3, 'SMACP3', 'Jose@gmail.com', '123', 'Jose', 'Manalo', 'BSHM'),
(4, 'SMACP4', 'gen.flem@gmail.com', '123456', 'Gennarino', ' Flemming', 'SCITE'),
(5, 'SMACP5', 'kingjames@gmail.com', '123456', 'Lebron', 'James', 'BSED'),
(6, 'SMACP6', 'j.mariano@gmail.com', '123456', 'Joanna', 'Mariano', 'SCITE');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `grade`
--
ALTER TABLE `grade`
  ADD PRIMARY KEY (`grade_id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`school_year`,`semester`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `registrar`
--
ALTER TABLE `registrar`
  ADD PRIMARY KEY (`registrar_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `teacher_code` (`teacher_code`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `grade`
--
ALTER TABLE `grade`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `registrar`
--
ALTER TABLE `registrar`
  MODIFY `registrar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `grade`
--
ALTER TABLE `grade`
  ADD CONSTRAINT `grade_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grade_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `subject_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
