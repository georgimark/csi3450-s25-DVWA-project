# [Martial Arts R US] 

A back/front end implementation of a Martial Arts administration database

---

## 📝 Overview

Here is a breakdown of file structure and recreating our results

---

## 📂 File Structure

Here’s a breakdown of the most important files and directories in this project.

* **`martial_arts.sql`**: The backend of our database, all SQL. The tables, procedures, and triggers.
* **`DVWA-DataDictionary.pdf`**: Picture of Data Dictionary.
* **`DVWA-RelationalDiagram.pdf`**: Picture of Relational Diagram.
* **`erd_DVWA.html`**: The mermaid of our ERD.
* **`Business Rules.txt`**: Business Rules of the Database.
* **`*.php`**: The front-end php files used for the website.
* **`README.md`**: The file you are currently reading.

---

## ⚙️ Getting Started

Follow these instructions to get a copy of the project up and running on your local machine.

### Prerequisites

List any software, libraries, or tools that users need to install before they can use your project.

```bash
Follow the XAMPP Setup from begining of semester
```

### Installation

Provide a step-by-step guide on how to set up the development environment.

1.  **Create a Database called MARU**
2.  **Import the martial_arts.sql database**
3.  **Setup Database configuration in db.php**
---

## ▶️ How to Run the Code

Here are the commands to execute the project after installation.

**To access the front-end:**

```bash
Once XAMPP is up and running, go to localhost/csi3450-s25-dvwa-project/index.php
```


Chapter 5: Page 184 – Case P8:

“Martial Arts R Us” (MARU) needs a database. MARU is a martial arts school with hundreds of students. The database must keep track of all of the classes that are offered, who is assigned to teach each class, and which students attend each class. Also, it is important to track the progress of each student as they advance. Create a complete Crow’s Foot ERD for these requirements:

•	Students are given a student number when they join the school. The number is stored along with their name, date of birth, and the date they joined the school.

•	All instructors are also students, but clearly not all students are instructors. In addition to the normal student information, for all instructors, the date that they start working as an instructor must be recorded along with their instructor status (compensated or volunteer).

•	An instructor may be assigned to teach any number of classes, but each class has one and only one assigned instructor. Some instructors, especially volunteer instructors may not be assigned to any class.

•	A class is offered for a specific level at a specific time, day of the week, and location. For example, one class taught on Mondays at 5:00 p.m. in Room 1 is an intermediate-level class. Another class taught on Mondays at 6:00 p.m. in Room 1 is a beginner-level class. A third class taught on Tuesdays at 5:00 p.m. in Room 2 is an advanced-level class.

•	Students may attend any class of the appropriate level during each week, so there is no expectation that any particular student will attend any particular class session. Therefore, the attendance of students at each individual class meeting must be tracked.

•	A student will attend many different class meetings, and each class meeting is normally attended by many students. Some class meetings may not be attended by any students. New students may not have attended any class meetings yet.

•	At any given meeting of a class, instructors other than the assigned instructor may show up to help. Therefore, a given class meeting may have a head instructor and many assistant instructors, but it will always have at least one instructor who is assigned to that class. For each class meeting, the date of the class and the instructors’ roles (head instructor or assistant instructor) need to be recorded. For example, Mr. Jones is assigned to teach the Monday, 5:00 p.m., intermediate class in Room 1. During a particular meeting of that class, Mr. Jones was the head instructor and Ms. Chen served as an assistant instructor.

•	Each student holds a rank in the martial arts. The rank name, belt color, and rank requirements are stored. Most ranks have numerous rank requirements, but each requirement is associated with only one particular rank. All ranks except white belt have at least one requirement.

•	A given rank may be held by many students. While it is customary to think of a student as having a single rank, it is necessary to track each student’s progress through the ranks. Therefore, every rank that a student attains is kept in the system. New students joining the school are automatically given the rank of white belt. The date that a student is awarded each rank should be kept in the system. All ranks have at least one student who has achieved that rank at some time.