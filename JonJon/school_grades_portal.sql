-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 23, 2026 at 04:17 PM
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
(5, 3, 3, 3, '2025-2026', '2ND', 89.00, 85.00, 87.00, 87.00);

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
(2, 'S-0001', 'test@test.com', '$2y$10$WBW2Viz9CRl8tDONqTuT9.r1VETLNv2/rkVTZfpQvLTyS.8Cvxag6', 'test', 'test', '2nd Year', '2B', 'Bachelor of Science in Information Technology'),
(3, 'S-0002', 'ranielbuan@gmail.com', '$2y$10$vcQrapCmHw.9BXiW6RedJOfvqlg6dGH0PnVqV/zJd7x2wQNiz/gTO', 'Jon Raniel', 'Buan', '2nd Year', '2A', 'Bachelor of Science in Information Technology');

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
(3, 'PFIT0002', 'PFIT 2', 'erm', 2, 3);

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
(3, 'SMACP3', 'Jose@gmail.com', '123', 'Jose', 'Manalo', 'BSHM');

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
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `registrar`
--
ALTER TABLE `registrar`
  MODIFY `registrar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
