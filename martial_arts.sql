SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `teaching_assignment`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `class_meeting`;
DROP TABLE IF EXISTS `class`;
DROP TABLE IF EXISTS `student_rank`;
DROP TABLE IF EXISTS `rank_requirement`;
DROP TABLE IF EXISTS `rank`;
DROP TABLE IF EXISTS `instructor`;
DROP TABLE IF EXISTS `student`;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- Tables
-- =========================

CREATE TABLE `student` (
  `student_no` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(60) NOT NULL,
  `last_name`  VARCHAR(60) NOT NULL,
  `dob`        DATE NOT NULL,
  `join_date`  DATE NOT NULL,
  PRIMARY KEY (`student_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Instructor is a subtype of Student (same PK, FK to student)
CREATE TABLE `instructor` (
  `student_no` INT UNSIGNED NOT NULL,
  `instructor_start_date` DATE NOT NULL,
  `instructor_status` ENUM('COMPENSATED','VOLUNTEER') NOT NULL,
  PRIMARY KEY (`student_no`),
  CONSTRAINT `fk_instructor_student`
    FOREIGN KEY (`student_no`) REFERENCES `student`(`student_no`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Classes offered (one assigned instructor required)
CREATE TABLE `class` (
  `class_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` ENUM('Beginner','Intermediate','Advanced') NOT NULL,
  `day_of_week` ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` TIME NOT NULL,
  `location`   VARCHAR(64) NOT NULL,
  `assigned_instructor_no` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `uq_class_slot` (`day_of_week`,`start_time`,`location`),
  CONSTRAINT `fk_class_instructor`
    FOREIGN KEY (`assigned_instructor_no`) REFERENCES `instructor`(`student_no`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Individual occurrences of a class
CREATE TABLE `class_meeting` (
  `meeting_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_id`   INT UNSIGNED NOT NULL,
  `meeting_date` DATE NOT NULL,
  PRIMARY KEY (`meeting_id`),
  UNIQUE KEY `uq_class_meeting` (`class_id`,`meeting_date`),
  KEY `idx_meeting_date` (`meeting_date`),
  CONSTRAINT `fk_meeting_class`
    FOREIGN KEY (`class_id`) REFERENCES `class`(`class_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance (M:N between students and meetings)
CREATE TABLE `attendance` (
  `meeting_id` INT UNSIGNED NOT NULL,
  `student_no` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`meeting_id`,`student_no`),
  KEY `idx_att_student` (`student_no`),
  CONSTRAINT `fk_att_meeting`
    FOREIGN KEY (`meeting_id`) REFERENCES `class_meeting`(`meeting_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_att_student`
    FOREIGN KEY (`student_no`) REFERENCES `student`(`student_no`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Instructors present at each meeting (head/assistant)
CREATE TABLE `teaching_assignment` (
  `meeting_id` INT UNSIGNED NOT NULL,
  `student_no` INT UNSIGNED NOT NULL,
  `role` ENUM('HEAD','ASSISTANT') NOT NULL,
  PRIMARY KEY (`meeting_id`,`student_no`),
  KEY `idx_ta_meeting_role` (`meeting_id`,`role`),
  CONSTRAINT `fk_ta_meeting`
    FOREIGN KEY (`meeting_id`) REFERENCES `class_meeting`(`meeting_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ta_instructor`
    FOREIGN KEY (`student_no`) REFERENCES `instructor`(`student_no`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ranks and requirements
CREATE TABLE `rank` (
  `rank_name`  VARCHAR(50) NOT NULL,
  `belt_color` VARCHAR(30) NOT NULL,
  PRIMARY KEY (`rank_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `rank_requirement` (
  `requirement_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rank_name` VARCHAR(50) NOT NULL,
  `requirement_description` TEXT NOT NULL,
  PRIMARY KEY (`requirement_id`),
  KEY `idx_req_rank` (`rank_name`),
  CONSTRAINT `fk_req_rank`
    FOREIGN KEY (`rank_name`) REFERENCES `rank`(`rank_name`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Each rank a student has earned (with award date)
CREATE TABLE `student_rank` (
  `student_no` INT UNSIGNED NOT NULL,
  `rank_name`  VARCHAR(50) NOT NULL,
  `date_awarded` DATE NOT NULL,
  PRIMARY KEY (`student_no`,`rank_name`),
  KEY `idx_sr_rank` (`rank_name`),
  CONSTRAINT `fk_sr_student`
    FOREIGN KEY (`student_no`) REFERENCES `student`(`student_no`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sr_rank`
    FOREIGN KEY (`rank_name`) REFERENCES `rank`(`rank_name`)
    ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================
-- work in progress section, will have triggers and procedures
-- =========================

-- Adding white belt rank
INSERT INTO `rank` (`rank_name`,`belt_color`)
VALUES ('White Belt','White')
ON DUPLICATE KEY UPDATE `belt_color`=VALUES(`belt_color`);

