<?php
// header.php
// This file provides the HTML head, page title, and the main navigation bar.
// It will be included at the top of every page.
require_once 'db.php'; // Ensure database connection is available.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set on each individual page -->
    <title><?php echo $page_title ?? 'Martial Arts Admin'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>

    <div class="container">
        <header>
            <h1>🥋 Martial Arts School Admin</h1>
            <nav class="main-nav">
                <a href="index.php">Dashboard</a>
                <a href="students.php">Students</a>
                <a href="instructors.php">Instructors</a>
                <a href="classes.php">Classes</a>
                <a href="ranks.php">Ranks</a>
                <a href="schedule.php">Schedule</a>
            </nav>
        </header>

        <main>
            <!-- The main content of each page will go here -->
            <p>Welcome to the Martial Arts R Us (MARU) Admin webpage! Here you view,<br>
            create, update, or delete the information of students, instructors, classes,<br>
            ranks, and schedules!</p>
        </main>
            
        <footer>
            <p>Front-end for MARU database. Group DVWA</p>
        </footer>
    </div>
</body>
</html>
<?php
// Close the database connection at the very end of the page load.
if (isset($conn)) {
    mysqli_close($conn);
}
?>
            