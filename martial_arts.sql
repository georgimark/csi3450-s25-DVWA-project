-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 11, 2025 at 02:07 PM
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
-- Database: `maru`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_assign_assistant` (IN `p_meeting_id` INT UNSIGNED, IN `p_student_no` INT UNSIGNED)   BEGIN
  IF NOT EXISTS (SELECT 1 FROM instructor WHERE student_no=p_student_no) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Assistant must be an INSTRUCTOR (subtype of STUDENT).';
  END IF;

  INSERT IGNORE INTO teaching_assignment(meeting_id,student_no,role)
  VALUES(p_meeting_id,p_student_no,'ASSISTANT');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_class` (IN `p_level` ENUM('Beginner','Intermediate','Advanced'), IN `p_day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), IN `p_start_time` TIME, IN `p_room_label` VARCHAR(64), IN `p_assigned_instructor_no` INT UNSIGNED, OUT `p_class_id` INT UNSIGNED)   BEGIN
  DECLARE v_location_id INT UNSIGNED;

  -- verify instructor exists
  IF NOT EXISTS (SELECT 1 FROM instructor WHERE student_no = p_assigned_instructor_no) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Assigned instructor does not exist in INSTRUCTOR.';
  END IF;

  CALL sp_upsert_location(p_room_label, v_location_id);

  INSERT INTO class(level,day_of_week,start_time,location_id,assigned_instructor_no)
  VALUES(p_level,p_day_of_week,p_start_time,v_location_id,p_assigned_instructor_no);

  SET p_class_id = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_meeting` (IN `p_class_id` INT UNSIGNED, IN `p_meeting_date` DATE, OUT `p_meeting_id` INT UNSIGNED)   BEGIN
  INSERT INTO class_meeting(class_id,meeting_date)
  VALUES(p_class_id,p_meeting_date);
  SET p_meeting_id = LAST_INSERT_ID();
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_create_student` (IN `p_first_name` VARCHAR(60), IN `p_last_name` VARCHAR(60), IN `p_dob` DATE, IN `p_join_date` DATE, OUT `p_student_no` INT UNSIGNED)   BEGIN
  INSERT INTO student(first_name,last_name,dob,join_date)
  VALUES(p_first_name,p_last_name,p_dob,p_join_date);
  SET p_student_no = LAST_INSERT_ID();

  -- ensure the rank exists (you already seed White Belt)
  IF NOT EXISTS (SELECT 1 FROM belt_rank WHERE rank_name='White Belt') THEN
    INSERT INTO belt_rank(rank_name,belt_color) VALUES('White Belt','White');
  END IF;

  -- award White Belt (idempotent correction of date if re-run)
  INSERT INTO student_rank(student_no,rank_name,date_awarded)
  VALUES(p_student_no,'White Belt',p_join_date)
  ON DUPLICATE KEY UPDATE date_awarded = VALUES(date_awarded);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_daily_schedule` (IN `p_date` DATE)   BEGIN
  SELECT
    m.meeting_id,
    c.class_id,
    c.level,
    c.day_of_week,
    c.start_time,
    l.room_label,
    -- Head
    (SELECT s.first_name FROM teaching_assignment ta
       JOIN student s ON s.student_no=ta.student_no
     WHERE ta.meeting_id=m.meeting_id AND ta.role='HEAD'
     LIMIT 1) AS head_first_name,
    (SELECT s.last_name FROM teaching_assignment ta
       JOIN student s ON s.student_no=ta.student_no
     WHERE ta.meeting_id=m.meeting_id AND ta.role='HEAD'
     LIMIT 1) AS head_last_name,
    -- Assistants (comma separated names)
    (SELECT GROUP_CONCAT(CONCAT(s.first_name,' ',s.last_name) ORDER BY s.last_name SEPARATOR ', ')
       FROM teaching_assignment ta
       JOIN student s ON s.student_no=ta.student_no
     WHERE ta.meeting_id=m.meeting_id AND ta.role='ASSISTANT') AS assistants
  FROM class_meeting m
  JOIN class c ON c.class_id=m.class_id
  JOIN location l ON l.location_id=c.location_id
  WHERE m.meeting_date = p_date
  ORDER BY c.start_time, l.room_label;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_meeting_attendance` (IN `p_meeting_id` INT UNSIGNED)   BEGIN
  SELECT a.student_no, s.first_name, s.last_name
  FROM attendance a
  JOIN student s ON s.student_no=a.student_no
  WHERE a.meeting_id=p_meeting_id
  ORDER BY s.last_name, s.first_name;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_get_student_profile` (IN `p_student_no` INT UNSIGNED, IN `p_from` DATE, IN `p_to` DATE)   BEGIN
  -- bio
  SELECT * FROM student WHERE student_no=p_student_no;

  -- ranks
  SELECT sr.rank_name, br.belt_color, sr.date_awarded
  FROM student_rank sr
  JOIN belt_rank br ON br.rank_name=sr.rank_name
  WHERE sr.student_no=p_student_no
  ORDER BY sr.date_awarded;

  -- attendance summary
  SELECT COUNT(*) AS meetings_attended
  FROM attendance a
  JOIN class_meeting m ON m.meeting_id=a.meeting_id
  WHERE a.student_no=p_student_no AND m.meeting_date BETWEEN p_from AND p_to;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_promote_student` (IN `p_student_no` INT UNSIGNED, IN `p_rank_name` VARCHAR(50), IN `p_date_awarded` DATE)   BEGIN
  IF NOT EXISTS (SELECT 1 FROM belt_rank WHERE rank_name=p_rank_name) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Rank does not exist in BELT_RANK.';
  END IF;

  INSERT INTO student_rank(student_no,rank_name,date_awarded)
  VALUES(p_student_no,p_rank_name,p_date_awarded)
  ON DUPLICATE KEY UPDATE date_awarded=VALUES(date_awarded);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_reassign_class_head` (IN `p_class_id` INT UNSIGNED, IN `p_new_instructor_no` INT UNSIGNED, IN `p_effective_from` DATE)   BEGIN
  -- validate
  IF NOT EXISTS (SELECT 1 FROM instructor WHERE student_no=p_new_instructor_no) THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='New Head must exist in INSTRUCTOR.';
  END IF;

  UPDATE class
  SET assigned_instructor_no = p_new_instructor_no
  WHERE class_id = p_class_id;

  -- Update HEAD rows in-place (no delete; your BEFORE DELETE trigger would block deletes anyway)
  UPDATE teaching_assignment ta
  JOIN class_meeting m ON m.meeting_id = ta.meeting_id
  SET ta.student_no = p_new_instructor_no
  WHERE m.class_id = p_class_id
    AND m.meeting_date >= p_effective_from
    AND ta.role = 'HEAD';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_schedule_meetings_for_range` (IN `p_class_id` INT UNSIGNED, IN `p_start` DATE, IN `p_end` DATE)   BEGIN
  DECLARE v_day ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
  DECLARE v_cur DATE;

  IF p_end < p_start THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='End date is before start date.';
  END IF;

  SELECT day_of_week INTO v_day FROM class WHERE class_id = p_class_id;
  IF v_day IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Class not found.';
  END IF;

  -- align first date to class day_of_week
  SET v_cur = p_start;
  WHILE DAYNAME(v_cur) <> v_day DO
    SET v_cur = DATE_ADD(v_cur, INTERVAL 1 DAY);
  END WHILE;

  WHILE v_cur <= p_end DO
    -- UNIQUE (class_id, meeting_date) prevents duplicates
    INSERT IGNORE INTO class_meeting(class_id, meeting_date) VALUES(p_class_id, v_cur);
    SET v_cur = DATE_ADD(v_cur, INTERVAL 7 DAY);
  END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_set_attendance` (IN `p_meeting_id` INT UNSIGNED, IN `p_student_no` INT UNSIGNED, IN `p_present` TINYINT)   BEGIN
  IF p_present IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='present must be 0 or 1.';
  END IF;

  IF p_present = 1 THEN
    INSERT IGNORE INTO attendance(meeting_id,student_no) VALUES(p_meeting_id,p_student_no);
  ELSE
    DELETE FROM attendance WHERE meeting_id=p_meeting_id AND student_no=p_student_no;
  END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_upsert_location` (IN `p_room_label` VARCHAR(64), OUT `p_location_id` INT UNSIGNED)   BEGIN
  INSERT INTO location (room_label) VALUES (p_room_label)
  ON DUPLICATE KEY UPDATE room_label = VALUES(room_label);
  SELECT location_id INTO p_location_id
  FROM location WHERE room_label = p_room_label;
END$$

DELIMITER ;

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
