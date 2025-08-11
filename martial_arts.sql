-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 11, 2025 at 02:05 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS teaching_assignment;
DROP TABLE IF EXISTS attendance;
DROP TABLE IF EXISTS class_meeting;
DROP TABLE IF EXISTS class;
DROP TABLE IF EXISTS student_rank;
DROP TABLE IF EXISTS rank_requirement;
DROP TABLE IF EXISTS belt_rank;
DROP TABLE IF EXISTS instructor;
DROP TABLE IF EXISTS student;
DROP TABLE IF EXISTS location;
SET FOREIGN_KEY_CHECKS = 1;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `MARU`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `meeting_id` int(10) UNSIGNED NOT NULL,
  `student_no` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `belt_rank`
--

CREATE TABLE `belt_rank` (
  `rank_name` varchar(50) NOT NULL,
  `belt_color` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `belt_rank`
--

INSERT INTO `belt_rank` (`rank_name`, `belt_color`) VALUES
('White Belt', 'White');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(10) UNSIGNED NOT NULL,
  `level` enum('Beginner','Intermediate','Advanced') NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `location_id` int(10) UNSIGNED NOT NULL,
  `assigned_instructor_no` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_meeting`
--

CREATE TABLE `class_meeting` (
  `meeting_id` int(10) UNSIGNED NOT NULL,
  `class_id` int(10) UNSIGNED NOT NULL,
  `meeting_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `class_meeting`
--
DELIMITER $$
CREATE TRIGGER `trg_class_meeting_add_head` AFTER INSERT ON `class_meeting` FOR EACH ROW BEGIN
  DECLARE head_instructor INT UNSIGNED;
  SELECT assigned_instructor_no INTO head_instructor
  FROM class WHERE class_id = NEW.class_id;

  IF head_instructor IS NOT NULL THEN
    INSERT IGNORE INTO teaching_assignment (meeting_id, student_no, role)
    VALUES (NEW.meeting_id, head_instructor, 'HEAD');
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `student_no` int(10) UNSIGNED NOT NULL,
  `instructor_start_date` date NOT NULL,
  `instructor_status` enum('COMPENSATED','VOLUNTEER') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(10) UNSIGNED NOT NULL,
  `room_label` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rank_requirement`
--

CREATE TABLE `rank_requirement` (
  `requirement_id` int(10) UNSIGNED NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `requirement_description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_no` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(60) NOT NULL,
  `last_name` varchar(60) NOT NULL,
  `dob` date NOT NULL,
  `join_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_rank`
--

CREATE TABLE `student_rank` (
  `student_no` int(10) UNSIGNED NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `date_awarded` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teaching_assignment`
--

CREATE TABLE `teaching_assignment` (
  `meeting_id` int(10) UNSIGNED NOT NULL,
  `student_no` int(10) UNSIGNED NOT NULL,
  `role` enum('HEAD','ASSISTANT') NOT NULL,
  `head_meeting_id` int(10) UNSIGNED GENERATED ALWAYS AS (case when `role` = 'HEAD' then `meeting_id` else NULL end) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `teaching_assignment`
--
DELIMITER $$
CREATE TRIGGER `trg_ta_head_must_match_class` BEFORE INSERT ON `teaching_assignment` FOR EACH ROW BEGIN
  IF NEW.role='HEAD' THEN
    IF NEW.student_no <> (
      SELECT c.assigned_instructor_no
      FROM class_meeting m
      JOIN class c ON c.class_id = m.class_id
      WHERE m.meeting_id = NEW.meeting_id
    ) THEN
      SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT='HEAD must be the class assigned instructor for this meeting';
    END IF;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_ta_prevent_head_delete` BEFORE DELETE ON `teaching_assignment` FOR EACH ROW BEGIN
  IF OLD.role='HEAD' THEN
    SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT='Cannot delete HEAD assignment; change the class assigned instructor instead.';
  END IF;
END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`meeting_id`,`student_no`),
  ADD KEY `idx_att_student` (`student_no`);

--
-- Indexes for table `belt_rank`
--
ALTER TABLE `belt_rank`
  ADD PRIMARY KEY (`rank_name`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`),
  ADD UNIQUE KEY `uq_class_slot` (`day_of_week`,`start_time`,`location_id`),
  ADD KEY `fk_class_location` (`location_id`),
  ADD KEY `fk_class_instructor` (`assigned_instructor_no`);

--
-- Indexes for table `class_meeting`
--
ALTER TABLE `class_meeting`
  ADD PRIMARY KEY (`meeting_id`),
  ADD UNIQUE KEY `uq_class_meeting` (`class_id`,`meeting_date`),
  ADD KEY `idx_meeting_date` (`meeting_date`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`student_no`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`),
  ADD UNIQUE KEY `uq_room_label` (`room_label`);

--
-- Indexes for table `rank_requirement`
--
ALTER TABLE `rank_requirement`
  ADD PRIMARY KEY (`requirement_id`),
  ADD KEY `idx_req_rank` (`rank_name`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_no`);

--
-- Indexes for table `student_rank`
--
ALTER TABLE `student_rank`
  ADD PRIMARY KEY (`student_no`,`rank_name`),
  ADD KEY `idx_sr_rank` (`rank_name`);

--
-- Indexes for table `teaching_assignment`
--
ALTER TABLE `teaching_assignment`
  ADD PRIMARY KEY (`meeting_id`,`student_no`),
  ADD UNIQUE KEY `uq_one_head_per_meeting` (`head_meeting_id`),
  ADD KEY `idx_ta_meeting_role` (`meeting_id`,`role`),
  ADD KEY `fk_ta_instructor` (`student_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_meeting`
--
ALTER TABLE `class_meeting`
  MODIFY `meeting_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rank_requirement`
--
ALTER TABLE `rank_requirement`
  MODIFY `requirement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_no` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_att_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `class_meeting` (`meeting_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_att_student` FOREIGN KEY (`student_no`) REFERENCES `student` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `fk_class_instructor` FOREIGN KEY (`assigned_instructor_no`) REFERENCES `instructor` (`student_no`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_class_location` FOREIGN KEY (`location_id`) REFERENCES `location` (`location_id`) ON UPDATE CASCADE;

--
-- Constraints for table `class_meeting`
--
ALTER TABLE `class_meeting`
  ADD CONSTRAINT `fk_meeting_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructor`
--
ALTER TABLE `instructor`
  ADD CONSTRAINT `fk_instructor_student` FOREIGN KEY (`student_no`) REFERENCES `student` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rank_requirement`
--
ALTER TABLE `rank_requirement`
  ADD CONSTRAINT `fk_req_rank` FOREIGN KEY (`rank_name`) REFERENCES `belt_rank` (`rank_name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_rank`
--
ALTER TABLE `student_rank`
  ADD CONSTRAINT `fk_sr_rank` FOREIGN KEY (`rank_name`) REFERENCES `belt_rank` (`rank_name`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sr_student` FOREIGN KEY (`student_no`) REFERENCES `student` (`student_no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teaching_assignment`
--
ALTER TABLE `teaching_assignment`
  ADD CONSTRAINT `fk_ta_instructor` FOREIGN KEY (`student_no`) REFERENCES `instructor` (`student_no`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ta_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `class_meeting` (`meeting_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
